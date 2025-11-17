<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTemplate;
use App\Models\User;
use App\Models\WorkSession;
use App\Http\Requests\CreateProjectRequest;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil daftar proyek yang user terlibat sebagai member
        $user = Auth::user();
        
        // Jika user belum login, return unauthorized
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Admin melihat semua proyek (karena admin adalah pembuat/pengelola sistem)
        if ($user->role === 'admin') {
            $projects = Project::with(['creator', 'leader'])
                ->withCount('members')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // User biasa hanya melihat proyek yang mereka terlibat sebagai member
            // ATAU proyek yang mereka buat (sebagai leader)
            $memberProjects = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->pluck('project_id')
                ->toArray();
                
            // Ambil project_id dari projects dimana leader_id adalah user saat ini
            $ledProjects = DB::table('projects')
                ->where('leader_id', $user->user_id)
                ->pluck('project_id')
                ->toArray();
                
            $projectIds = array_merge($memberProjects, $ledProjects);
            
            $projects = Project::whereIn('project_id', $projectIds)
                ->with(['creator', 'leader'])
                ->withCount('members')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        // Return JSON for API requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'projects' => $projects
            ]);
        }
        
        // Hitung statistik untuk view
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $activeMembers = DB::table('project_members')
            ->distinct('user_id')
            ->count('user_id');
        $highPriorityProjects = Project::where('priority', 'high')->count();
        
        // Completed projects statistics
        $completedProjects = Project::where('status', 'completed')->count();
        $completedOnTime = Project::where('status', 'completed')
            ->where('is_overdue', false)
            ->count();
        $completedLate = Project::where('status', 'completed')
            ->where('is_overdue', true)
            ->count();
        
        // Get completed projects list with details
        $completedProjectsList = Project::where('status', 'completed')
            ->with(['creator', 'leader'])
            ->withCount('members')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function($project) {
                return [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'leader_name' => $project->leader ? $project->leader->full_name : '-',
                    'deadline' => $project->deadline ? $project->deadline->format('d M Y') : '-',
                    'completed_at' => $project->completed_at ? $project->completed_at->format('d M Y H:i') : '-',
                    'is_overdue' => $project->is_overdue,
                    'delay_days' => $project->delay_days ?? 0,
                    'delay_message' => $project->getDelayMessage(),
                    'badge_color' => $project->getDelayBadgeColor(),
                    'completion_notes' => $project->completion_notes,
                    'delay_reason' => $project->delay_reason,
                    'members_count' => $project->members_count
                ];
            });
        
        // Ambil semua kategori jika ada
        $categories = DB::table('projects')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->get()
            ->map(function($item) {
                return (object)['id' => $item->category, 'name' => $item->category];
            });
        
        // Get active leaders for create modal
        $leaders = User::where('role', 'leader')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['user_id', 'full_name', 'username', 'email']);
        
        // Return view for web requests
        return view('admin.projects.index', [
            'projects' => $projects,
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'activeMembers' => $activeMembers,
            'highPriorityProjects' => $highPriorityProjects,
            'completedProjects' => $completedProjects,
            'completedOnTime' => $completedOnTime,
            'completedLate' => $completedLate,
            'completedProjectsList' => $completedProjectsList,
            'categories' => $categories,
            'leaders' => $leaders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only admin can access this
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat membuat project.');
        }
        
        // Get active project templates if table exists
        $templates = [];
        try {
            $templates = ProjectTemplate::active()
                ->orderByDesc('usage_count')
                ->get();
        } catch (\Exception $e) {
            // Table might not exist, ignore
        }
            
        // Get available leaders
        $leaders = User::where('role', 'leader')
            ->where('status', 'active')
            ->select('user_id', 'name', 'email')
            ->get();
        
        return view('admin.projects.create', compact('templates', 'leaders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'project_name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                // Custom unique rule yang mengabaikan soft deleted records
                \Illuminate\Validation\Rule::unique('projects', 'project_name')->whereNull('deleted_at')
            ],
            'description' => 'nullable|string',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|in:web_development,mobile_app,desktop_software,data_analysis,marketing,design,research,other',
            'leader_id' => [
                'required',
                'exists:users,user_id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if (!$user || $user->role !== 'leader') {
                        $fail('User yang dipilih harus memiliki role Leader.');
                    }
                    if ($user->status !== 'active') {
                        $fail('Leader harus dalam status aktif.');
                    }
                },
            ],
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'public_visibility' => 'nullable|boolean',
            'allow_member_invite' => 'nullable|boolean',
            'notifications_enabled' => 'nullable|boolean',
        ], [
            'project_name.required' => 'Nama proyek wajib diisi.',
            'project_name.unique' => 'Nama proyek sudah digunakan. Pilih nama yang berbeda.',
            'leader_id.required' => 'Team Leader wajib dipilih.',
            'leader_id.exists' => 'Team Leader tidak valid.',
        ]);

        DB::beginTransaction();
        
        try {
            // Create the project
            $project = Project::create([
                'project_name' => $request->project_name,
                'description' => $request->description,
                'status' => $request->status ?? 'planning',
                'deadline' => $request->end_date,
                'priority' => $request->priority ?? 'medium',
                'category' => $request->category ?? 'other',
                'budget' => $request->budget,
                'leader_id' => $request->leader_id,
                'notifications_enabled' => $request->boolean('notifications_enabled', true),
                'public_visibility' => $request->boolean('public_visibility', false),
                'allow_member_invite' => $request->boolean('allow_member_invite', true),
                'created_by' => Auth::id(),
                'last_activity_at' => now(),
            ]);

            // Try to create initial boards (optional, don't fail if table doesn't exist)
            try {
                if (class_exists('\App\Models\Board')) {
                    $initialBoards = ['To Do', 'In Progress', 'Review', 'Done'];
                    foreach ($initialBoards as $index => $boardName) {
                        \App\Models\Board::create([
                            'project_id' => $project->project_id,
                            'name' => $boardName,
                            'position' => $index + 1,
                            'color' => $this->getDefaultBoardColor($index),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not create boards: ' . $e->getMessage());
            }

            // PERBAIKAN: Admin tidak ditambahkan sebagai project member otomatis
            // Hanya tambahkan leader sebagai project member (jika ada)
            if ($request->leader_id) {
                try {
                    ProjectMember::create([
                        'project_id' => $project->project_id,
                        'user_id' => $request->leader_id,
                        'role' => 'project_manager',
                        'joined_at' => now(),
                    ]);
                    
                    // Send notification to leader
                    $leader = User::find($request->leader_id);
                    $admin = Auth::user();
                    if ($leader && $admin) {
                        NotificationHelper::projectLeaderAssigned($project, $leader, $admin);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not add leader as member: ' . $e->getMessage());
                }
            }

            DB::commit();

            // Check if this is a draft save
            if ($request->save_as_draft) {
                $response = [
                    'success' => true,
                    'message' => 'Project draft saved successfully. You can continue editing or publish it later.',
                    'redirect' => route('projects.edit', $project->project_id)
                ];
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json($response);
                }
                
                return redirect()->route('projects.edit', $project->project_id)
                    ->with('success', $response['message']);
            }

            $successMessage = 'Proyek berhasil dibuat!' . 
                ($request->leader_id ? ' Team leader telah ditambahkan sebagai project manager.' : ' Anda dapat menambahkan anggota tim nanti.');
            
            $response = [
                'success' => true,
                'message' => $successMessage,
                'redirect' => route('admin.projects.index'),
                'project' => $project,
                'note' => 'Admin tidak otomatis ditambahkan sebagai anggota project.'
            ];
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('admin.projects.index')
                ->with('success', $successMessage);
                    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorMessage = 'Gagal membuat proyek. ';
            
            // Check specific error types
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMessage .= 'Nama proyek sudah digunakan.';
            } elseif (strpos($e->getMessage(), 'Column not found') !== false) {
                $errorMessage .= 'Ada kolom yang tidak ditemukan di database.';
            } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
                $errorMessage .= 'Kolom tidak valid: ' . $e->getMessage();
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            $errorResponse = [
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ];
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($errorResponse, 422);
            }
            
            return back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Get default board colors
     * 
     * @param int $index Board index
     * @return string Color hex code
     */
    private function getDefaultBoardColor($index)
    {
        /** @var int $index */
        
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
        return $colors[$index % count($colors)];
    }

    /**
     * Handle document uploads for project
     * 
     * @param Project $project Project instance
     * @param array $documents Array of uploaded files
     * @return void
     */
    private function handleDocumentUploads($project, $documents)
    {
        /** @var Project $project */
        /** @var array $documents */
        
        foreach ($documents as $document) {
            $filename = time() . '_' . $document->getClientOriginalName();
            $path = $document->storeAs('projects/' . $project->project_id . '/documents', $filename, 'public');
            
            // You might want to create a ProjectDocument model to track these files
            // For now, we'll just store them in the filesystem
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param Request $request HTTP request
     * @param string $id Project ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(Request $request, string $id)
    {
        /** @var Request $request */
        /** @var string $id */
        
        $user = Auth::user();
        
        // Ambil proyek dengan data creator, anggota dan board
        $project = Project::with([
            'creator:user_id,username,full_name',
            'members.user:user_id,username,full_name,email',
            'boards' => function($query) {
                $query->orderBy('position');
            },
            'boards.cards' => function($query) {
                $query->orderBy('due_date');
            }
        ])->findOrFail($id);
        
        // Check if user has access to this project using improved logic
        $hasAccess = $this->checkProjectAccess($user, $project);
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }
        
        // Return JSON for API requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            // Ambil statistik proyek dengan raw SQL dan function database
            $statistics = DB::select(
                'SELECT 
                    calculate_project_completion_rate(?) as completion_rate,
                    calculate_total_project_hours(?) as total_hours
                ', [$id, $id]
            );
            
            // Ambil statistik tugas dengan join
            $cardStatistics = DB::table('cards')
                ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                ->where('boards.project_id', $id)
                ->select(
                    DB::raw('COUNT(*) as total_cards'),
                    DB::raw('SUM(CASE WHEN cards.status = "todo" THEN 1 ELSE 0 END) as todo_count'),
                    DB::raw('SUM(CASE WHEN cards.status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count'),
                    DB::raw('SUM(CASE WHEN cards.status = "review" THEN 1 ELSE 0 END) as review_count'),
                    DB::raw('SUM(CASE WHEN cards.status = "done" THEN 1 ELSE 0 END) as done_count'),
                    DB::raw('SUM(cards.estimated_hours) as total_estimated_hours'),
                    DB::raw('SUM(cards.actual_hours) as total_actual_hours')
                )
                ->first();
            
            return response()->json([
                'project' => $project,
                'statistics' => $statistics[0] ?? null,
                'card_statistics' => $cardStatistics
            ]);
        }
        
        // Get task statistics for the project
        $taskStatistics = null;
        try {
            $taskService = app(\App\Services\TaskService::class);
            $taskStatistics = $taskService->getProjectTaskStatistics($id);
        } catch (\Exception $e) {
            // If task statistics fails, just set to null
            $taskStatistics = [
                'total' => 0,
                'todo' => 0,
                'in_progress' => 0,
                'review' => 0,
                'done' => 0,
                'overdue' => 0,
                'completion_rate' => 0,
            ];
        }
        
        // Get work sessions history for this project with filters
        $workSessionsQuery = WorkSession::whereHas('task', function($query) use ($id) {
                $query->where('project_id', $id);
            })
            ->with(['user:user_id,full_name,email', 'task:task_id,title'])
            ->where('status', 'completed');
        
        // Apply filters from request
        if ($request->has('date_from') && $request->date_from) {
            $workSessionsQuery->where('work_date', '>=', $request->date_from);
        } else {
            $workSessionsQuery->where('work_date', '>=', now()->subDays(30));
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $workSessionsQuery->where('work_date', '<=', $request->date_to);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $workSessionsQuery->where('user_id', $request->user_id);
        }
        
        $workSessions = $workSessionsQuery->orderBy('work_date', 'desc')
            ->orderBy('stopped_at', 'desc')
            ->paginate(15)
            ->withQueryString(); // Maintain query parameters in pagination links
        
        // Get today's total work time for this project
        $todayTotal = WorkSession::whereHas('task', function($query) use ($id) {
                $query->where('project_id', $id);
            })
            ->forDate(now())
            ->sum('duration_seconds');
        
        // Get this week's total
        $weekTotal = WorkSession::whereHas('task', function($query) use ($id) {
                $query->where('project_id', $id);
            })
            ->where('work_date', '>=', now()->startOfWeek())
            ->where('work_date', '<=', now()->endOfWeek())
            ->sum('duration_seconds');
        
        // Get month's total
        $monthTotal = WorkSession::whereHas('task', function($query) use ($id) {
                $query->where('project_id', $id);
            })
            ->whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->sum('duration_seconds');
        
        // Get most productive member this week
        $topContributor = WorkSession::whereHas('task', function($query) use ($id) {
                $query->where('project_id', $id);
            })
            ->where('work_date', '>=', now()->startOfWeek())
            ->select('user_id', DB::raw('SUM(duration_seconds) as total'))
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->with('user:user_id,full_name')
            ->first();
        
        // Return view for web requests
        return view('admin.projects.show', compact('project', 'taskStatistics', 'workSessions', 'todayTotal', 'weekTotal', 'monthTotal', 'topContributor'));
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @param Request $request HTTP request
     * @param string $id Project ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function edit(Request $request, string $id)
    {
        /** @var Request $request */
        /** @var string $id */
        
        $project = Project::findOrFail($id);
        $projectMembers = ProjectMember::where('project_id', $id)
            ->with('user:user_id,username,full_name')
            ->get();
        $allUsers = User::select('user_id', 'username', 'full_name')->get();
        
        // Return JSON for API requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'project' => $project,
                'members' => $projectMembers,
                'users' => $allUsers
            ]);
        }
        
        // Return view for web requests
        return view('admin.projects.edit', compact('project', 'projectMembers', 'allUsers'));
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request HTTP request
     * @param string $id Project ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        /** @var Request $request */
        /** @var string $id */
        
        $user = Auth::user();
        $project = Project::findOrFail($id);
        
        // Check permissions: 
        // - Admin dapat edit detail project tapi tidak bisa mengubah anggota
        // - Leader dapat edit project dan mengelola anggota
        $canEdit = false;
        $canManageMembers = false;
        
        if ($user->role === 'admin') {
            $canEdit = true;
            $canManageMembers = false; // BATASAN: Admin tidak bisa mengelola anggota
        } elseif ($project->leader_id === $user->user_id) {
            $canEdit = true;
            $canManageMembers = true; // Leader bisa mengelola anggota
        }
        
        if (!$canEdit) {
            return response()->json(['message' => 'You do not have permission to edit this project'], 403);
        }
        
        // Jika ada perubahan members dan user bukan leader
        if ($request->has('members') && !$canManageMembers) {
            return response()->json([
                'message' => 'Hanya team leader yang dapat mengelola anggota project'
            ], 403);
        }
        
        $request->validate([
            'project_name' => [
                'required',
                'string',
                'max:100',
                // Unique rule yang mengabaikan soft deleted records dan record saat ini
                \Illuminate\Validation\Rule::unique('projects', 'project_name')
                    ->whereNull('deleted_at')
                    ->ignore($id, 'project_id')
            ],
            'description' => 'nullable|string',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'public_visibility' => 'nullable|boolean',
            'allow_member_invite' => 'nullable|boolean',
            'notifications_enabled' => 'nullable|boolean',
            'is_archived' => 'nullable|boolean',
            'members' => 'nullable|array',
            'members.*.user_id' => 'exists:users,user_id',
            'members.*.role' => 'in:developer,designer', // Hapus project_manager
        ], [
            'project_name.required' => 'Nama proyek wajib diisi.',
            'project_name.unique' => 'Nama proyek sudah digunakan. Pilih nama yang berbeda.',
        ]);
        
        // Update project fields
        $project->project_name = $request->project_name;
        $project->description = $request->description;
        $project->deadline = $request->deadline;
        
        // Update additional fields if provided
        if ($request->has('status')) {
            $project->status = $request->status;
        }
        if ($request->has('priority')) {
            $project->priority = $request->priority;
        }
        if ($request->has('category')) {
            $project->category = $request->category;
        }
        if ($request->has('budget')) {
            $project->budget = $request->budget;
        }
        if ($request->has('completion_percentage')) {
            $project->completion_percentage = $request->completion_percentage;
        }
        if ($request->has('public_visibility')) {
            $project->public_visibility = $request->boolean('public_visibility');
        }
        if ($request->has('allow_member_invite')) {
            $project->allow_member_invite = $request->boolean('allow_member_invite');
        }
        if ($request->has('notifications_enabled')) {
            $project->notifications_enabled = $request->boolean('notifications_enabled');
        }
        if ($request->has('is_archived')) {
            $project->is_archived = $request->boolean('is_archived');
        }
        
        $project->last_activity_at = now();
        $project->save();
        
        // Update anggota proyek jika ada perubahan dan user adalah leader
        if ($request->has('members') && $canManageMembers) {
            // Hapus anggota yang lama (kecuali leader)
            ProjectMember::where('project_id', $id)
                ->whereHas('user', function($q) {
                    $q->whereIn('role', ['developer', 'designer']);
                })
                ->delete();
            
            // Tambahkan anggota baru (validasi hanya developer dan designer)
            foreach ($request->members as $member) {
                $user = User::find($member['user_id']);
                
                // Skip jika bukan developer atau designer
                if (!in_array($user->role, ['developer', 'designer'])) {
                    continue;
                }
                
                // Skip jika admin
                if ($user->role === 'admin') {
                    continue;
                }
                
                $projectMember = new ProjectMember();
                $projectMember->project_id = $project->project_id;
                $projectMember->user_id = $member['user_id'];
                $projectMember->role = $member['role'];
                $projectMember->save();
            }
        }
        
        // Return JSON for API requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil diperbarui',
                'project' => $project
            ]);
        }
        
        // Return redirect for web requests
        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        /** @var string $id */
        
        $project = Project::findOrFail($id);
        
        // Periksa apakah user adalah admin atau creator dari proyek
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus proyek ini'], 403);
        }
        
        // Hapus proyek (semua relasi akan terhapus karena kita menggunakan onDelete cascade)
        $project->delete();
        
        return response()->json([
            'message' => 'Proyek berhasil dihapus'
        ]);
    }
    
    /**
     * Mendapatkan statistik proyek secara detail
     * 
     * @param string $id Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectStatistics(string $id)
    {
        /** @var string $id */
        
        // Validasi proyek ada
        $project = Project::findOrFail($id);
        
        // Statistik dasar menggunakan function database
        $statistics = DB::select(
            'SELECT 
                calculate_project_completion_rate(?) as completion_rate,
                calculate_total_project_hours(?) as total_hours
            ', [$id, $id]
        );
        
        // Statistik anggota dengan jumlah tugas dan jam kerja (join complex)
        $memberStats = DB::table('project_members')
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->leftJoin('card_assignments', 'project_members.user_id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.card_id')
            ->leftJoin('boards', 'cards.board_id', '=', 'boards.board_id')
            ->leftJoin('time_logs', function($join) {
                $join->on('card_assignments.user_id', '=', 'time_logs.user_id')
                    ->on('card_assignments.card_id', '=', 'time_logs.card_id');
            })
            ->where('project_members.project_id', $id)
            ->where('boards.project_id', $id)
            ->select(
                'users.user_id',
                'users.username',
                'users.full_name',
                'project_members.role',
                DB::raw('COUNT(DISTINCT card_assignments.card_id) as assigned_tasks'),
                DB::raw('SUM(CASE WHEN card_assignments.assignment_status = "completed" THEN 1 ELSE 0 END) as completed_tasks'),
                DB::raw('SUM(time_logs.duration_seconds) / 3600 as total_hours') // Convert seconds to hours
            )
            ->groupBy('users.user_id', 'users.username', 'users.full_name', 'project_members.role')
            ->get();
            
        // Statistik aktivitas per minggu (untuk grafik)
        $weeklyActivity = DB::table('time_logs')
            ->join('cards', 'time_logs.card_id', '=', 'cards.card_id')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('boards.project_id', $id)
            ->select(
                DB::raw('YEAR(time_logs.start_time) as year'),
                DB::raw('WEEK(time_logs.start_time) as week'),
                DB::raw('SUM(time_logs.duration_seconds) / 3600 as hours_logged') // Convert seconds to hours
            )
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();
            
        return response()->json([
            'project' => $project,
            'statistics' => $statistics[0] ?? null,
            'member_statistics' => $memberStats,
            'weekly_activity' => $weeklyActivity
        ]);
    }

    /**
     * Check if user has access to the project
     * 
     * @param User $user User instance
     * @param Project $project Project instance
     * @return bool
     */
    private function checkProjectAccess($user, $project): bool
    {
        /** @var User $user */
        /** @var Project $project */
        
        // Admin has access to all projects (sebagai pengelola sistem)
        if ($user->role === 'admin') {
            return true;
        }

        // Check if user is the project creator (admin yang membuat)
        if ($project->created_by === $user->user_id) {
            return true;
        }

        // Check if user is the project leader
        if ($project->leader_id === $user->user_id) {
            return true;
        }

        // Check if user is a member of this project
        $isMember = $project->members->where('user_id', $user->user_id)->count() > 0;
        
        return $isMember;
    }
}
