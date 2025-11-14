<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\ProjectTemplate;
use App\Http\Requests\CreateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectControllerImproved extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role.admin')->only(['create', 'store', 'destroy']);
    }

    /**
     * Display a listing of projects with enhanced filtering
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $filter = $request->get('filter', 'all');
            $status = $request->get('status');
            $search = $request->get('search');
            
            $query = Project::query()
                ->with(['creator:user_id,full_name,email', 'projectManager'])
                ->withCount(['members', 'boards', 'cards']);

            // Apply role-based filtering
            if ($user->role === 'admin') {
                // Admin sees all projects
                $this->applyAdminFilters($query, $filter, $status, $search);
            } else {
                // Regular users see only their projects
                $this->applyUserFilters($query, $user, $filter);
            }

            // Apply search if provided
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $projects = $query->orderBy('created_at', 'desc')->paginate(12);

            // Calculate statistics for admin dashboard
            $stats = $this->getProjectStatistics($user);

            if ($request->wantsJson()) {
                return response()->json([
                    'projects' => $projects,
                    'stats' => $stats
                ]);
            }

            return view('admin.projects.index', compact('projects', 'stats', 'filter', 'status', 'search'));
            
        } catch (\Exception $e) {
            Log::error('Error loading projects: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to load projects'], 500);
            }
            
            return back()->with('error', 'Failed to load projects. Please try again.');
        }
    }

    /**
     * Show the form for creating a new project with templates
     */
    public function create()
    {
        try {
            // Get available project templates
            $templates = ProjectTemplate::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // Get available team leaders
            $leaders = User::where('role', 'leader')
                ->where('status', 'active')
                ->select('user_id', 'full_name', 'email', 'username')
                ->orderBy('full_name')
                ->get();

            // Get project categories/types
            $categories = $this->getProjectCategories();

            return view('admin.projects.create-improved', compact('templates', 'leaders', 'categories'));
            
        } catch (\Exception $e) {
            Log::error('Error loading project creation form: ' . $e->getMessage());
            return back()->with('error', 'Failed to load project creation form.');
        }
    }

    /**
     * Store a newly created project with enhanced validation
     */
    public function store(CreateProjectRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            
            // Create project
            $project = new Project([
                'name' => $request->project_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'priority' => $request->priority ?? 'medium',
                'category' => $request->category,
                'budget' => $request->budget,
                'created_by' => $user->user_id,
                'project_code' => $this->generateProjectCode($request->project_name),
                'settings' => json_encode([
                    'notifications_enabled' => $request->boolean('notifications_enabled', true),
                    'public_visibility' => $request->boolean('public_visibility', false),
                    'allow_member_invite' => $request->boolean('allow_member_invite', true),
                ])
            ]);

            $project->save();

            // Handle project documents upload
            $this->handleProjectDocuments($request, $project);

            // Assign team leader as project manager
            if ($request->leader_id) {
                $this->assignProjectManager($project, $request->leader_id);
            }

            // Apply project template if selected
            if ($request->template_id) {
                $this->applyProjectTemplate($project, $request->template_id);
            }

            // Create initial boards if specified
            $this->createInitialBoards($project, $request->initial_boards ?? []);

            // Send notifications
            $this->sendProjectCreationNotifications($project);

            DB::commit();

            Log::info('Project created successfully', [
                'project_id' => $project->project_id,
                'created_by' => $user->user_id,
                'project_name' => $project->name
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Project created successfully',
                    'project' => $project->load('creator', 'projectManager')
                ], 201);
            }

            return redirect()->route('projects.show', $project->project_id)
                           ->with('success', "Project '{$project->name}' has been created successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creating project: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'request_data' => $request->except(['documents'])
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Failed to create project',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
                ], 500);
            }

            return back()->withInput()
                        ->with('error', 'Failed to create project. Please try again.');
        }
    }

    /**
     * Display the specified project with comprehensive data
     */
    public function show(Project $project)
    {
        try {
            $user = Auth::user();
            
            // Check permission
            if (!$this->canUserViewProject($user, $project)) {
                abort(403, 'You do not have permission to view this project.');
            }

            $project->load([
                'creator:user_id,full_name,email',
                'projectManager:user_id,full_name,email',
                'members.user:user_id,full_name,email,role',
                'boards.cards',
                'documents'
            ]);

            // Get project statistics
            $projectStats = $this->getProjectDetailStats($project);

            // Get recent activities
            $recentActivities = $this->getProjectActivities($project, 10);

            // Get team members with roles
            $teamMembers = $this->getProjectTeamMembers($project);

            return view('admin.projects.show-improved', compact(
                'project', 
                'projectStats', 
                'recentActivities', 
                'teamMembers'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading project details: ' . $e->getMessage());
            return back()->with('error', 'Failed to load project details.');
        }
    }

    /**
     * Get available team leaders via AJAX
     */
    public function searchLeaders(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $leaders = User::where('role', 'leader')
                ->where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('full_name', 'LIKE', "%{$query}%")
                      ->orWhere('username', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->select('user_id', 'full_name', 'username', 'email')
                ->limit(10)
                ->get()
                ->map(function($leader) {
                    // Get current project count for this leader
                    $projectCount = ProjectMember::where('user_id', $leader->user_id)
                        ->where('role', 'project_manager')
                        ->count();
                    
                    return [
                        'user_id' => $leader->user_id,
                        'full_name' => $leader->full_name,
                        'username' => $leader->username,
                        'email' => $leader->email,
                        'current_projects' => $projectCount,
                        'availability' => $projectCount < 3 ? 'available' : 'busy' // Max 3 projects per leader
                    ];
                });

            return response()->json($leaders);
            
        } catch (\Exception $e) {
            Log::error('Error searching leaders: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search leaders'], 500);
        }
    }

    /**
     * Save project as draft
     */
    public function saveDraft(Request $request)
    {
        try {
            $user = Auth::user();
            
            $draftData = [
                'project_name' => $request->project_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'category' => $request->category,
                'leader_id' => $request->leader_id,
                'template_id' => $request->template_id,
                'priority' => $request->priority,
                'budget' => $request->budget,
                'saved_at' => now()
            ];

            // Save to session or cache
            session(['project_draft' => $draftData]);
            
            return response()->json([
                'message' => 'Draft saved successfully',
                'saved_at' => $draftData['saved_at']->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving project draft: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save draft'], 500);
        }
    }

    // Private helper methods

    private function applyAdminFilters($query, $filter, $status, $search)
    {
        switch ($filter) {
            case 'active':
                $query->where('status', 'active');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'overdue':
                $query->where('end_date', '<', now())
                      ->where('status', '!=', 'completed');
                break;
            case 'my_created':
                $query->where('created_by', Auth::id());
                break;
        }

        if ($status) {
            $query->where('status', $status);
        }
    }

    private function applyUserFilters($query, $user, $filter)
    {
        $memberProjects = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->pluck('project_id');
            
        $createdProjects = DB::table('projects')
            ->where('created_by', $user->user_id)
            ->pluck('project_id');
            
        $projectIds = $memberProjects->merge($createdProjects)->unique();
        
        $query->whereIn('project_id', $projectIds);
    }

    private function getProjectStatistics($user)
    {
        if ($user->role !== 'admin') {
            return null;
        }

        return [
            'total' => Project::count(),
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'planning' => Project::where('status', 'planning')->count(),
            'overdue' => Project::where('end_date', '<', now())
                              ->where('status', '!=', 'completed')
                              ->count(),
            'this_month' => Project::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count(),
        ];
    }

    private function generateProjectCode($projectName)
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $projectName), 0, 3));
        $number = Project::count() + 1;
        return $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    private function handleProjectDocuments($request, $project)
    {
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store("projects/{$project->project_id}/documents", 'public');
                
                DB::table('project_documents')->insert([
                    'project_id' => $project->project_id,
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function assignProjectManager($project, $leaderId)
    {
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $leaderId,
            'role' => 'project_manager',
            'joined_at' => now()
        ]);
    }

    private function applyProjectTemplate($project, $templateId)
    {
        $template = ProjectTemplate::find($templateId);
        if ($template && $template->boards_config) {
            foreach ($template->boards_config as $boardConfig) {
                // Create boards and cards based on template
                // Implementation depends on your Board/Card models
            }
        }
    }

    private function createInitialBoards($project, $boards)
    {
        foreach ($boards as $boardName) {
            if (!empty(trim($boardName))) {
                DB::table('boards')->insert([
                    'project_id' => $project->project_id,
                    'name' => trim($boardName),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function sendProjectCreationNotifications($project)
    {
        // Send notification to project manager
        if ($project->projectManager) {
            // Implement notification logic
        }
    }

    private function canUserViewProject($user, $project)
    {
        if ($user->role === 'admin') return true;
        if ($project->created_by === $user->user_id) return true;
        
        return ProjectMember::where('project_id', $project->project_id)
                           ->where('user_id', $user->user_id)
                           ->exists();
    }

    private function getProjectCategories()
    {
        return [
            'web_development' => 'Web Development',
            'mobile_app' => 'Mobile Application',
            'desktop_software' => 'Desktop Software',
            'data_analysis' => 'Data Analysis',
            'marketing' => 'Marketing Campaign',
            'design' => 'Design Project',
            'research' => 'Research & Development',
            'other' => 'Other'
        ];
    }

    private function getProjectDetailStats($project)
    {
        return [
            'total_members' => $project->members->count(),
            'total_boards' => $project->boards->count(),
            'total_tasks' => $project->cards->count(),
            'completed_tasks' => $project->cards->where('status', 'done')->count(),
            'progress_percentage' => $project->cards->count() > 0 
                ? round(($project->cards->where('status', 'done')->count() / $project->cards->count()) * 100) 
                : 0
        ];
    }

    private function getProjectActivities($project, $limit = 10)
    {
        // Implementation depends on your activity tracking system
        return collect();
    }

    private function getProjectTeamMembers($project)
    {
        return $project->members()
            ->with('user:user_id,full_name,email')
            ->get()
            ->map(function($member) {
                return [
                    'user' => $member->user,
                    'role' => $member->role,
                    'joined_at' => $member->joined_at,
                ];
            });
    }
}