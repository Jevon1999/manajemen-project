<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Card;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics
     */
    public function dashboard()
    {
        $totalTasks = Card::count();
        $completedTasks = Card::where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        
        // Overdue projects
        $overdueProjects = Project::where('deadline', '<', now())
            ->where('status', '!=', 'completed')
            ->count();
        
        // Upcoming deadlines (next 7 days)
        $upcomingDeadlines = Project::where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays(7))
            ->where('status', '!=', 'completed')
            ->with('creator')
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();
        
        // Count available leaders (no active projects)
        $availableLeadersCount = User::where('role', 'leader')
            ->whereDoesntHave('ledProjects', function($query) {
                $query->whereIn('status', ['active', 'planning', 'on_hold']);
            })
            ->count();
        
        // Project creation trend (last 6 months)
        $projectTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $projectTrend[] = [
                'month' => $month->format('M'),
                'count' => Project::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count()
            ];
        }
        
        // Task status distribution
        $taskDistribution = [
            'todo' => Card::where('status', 'todo')->count(),
            'in_progress' => Card::where('status', 'in_progress')->count(),
            'done' => $completedTasks,
        ];
        
        $stats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'overdue_projects' => $overdueProjects,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => Card::whereIn('status', ['todo', 'in_progress'])->count(),
            'completion_rate' => $completionRate,
            'available_leaders_count' => $availableLeadersCount,
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_projects = Project::with('creator')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_projects', 'upcomingDeadlines', 'projectTrend', 'taskDistribution'));
    }

    /**
     * Display comprehensive user management page
     */
    public function management()
    {
        // Get all users with their project counts
        $users = User::withCount(['projectMemberships', 'createdProjects', 'timeLogs'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get user statistics
        $stats = [
            'total_users' => $users->count(),
            'active_users' => $users->where('status', 'active')->count(),
            'inactive_users' => $users->where('status', 'inactive')->count(),
            'admin_users' => $users->where('role', 'admin')->count(),
            'leader_users' => $users->where('role', 'leader')->count(),
            'regular_users' => $users->where('role', 'user')->count(),
        ];
        
        // Get recent activities
        $recentUsers = User::latest()->take(10)->get();
        
        return view('admin.users.management', compact('users', 'stats', 'recentUsers'));
    }

    /**
     * Display comprehensive user management page (alias)
     */
    public function usersManagement()
    {
        // Get all users with their project counts
        $users = User::withCount(['projectMemberships', 'createdProjects', 'timeLogs'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get user statistics
        $stats = [
            'total_users' => $users->count(),
            'active_users' => $users->where('status', 'active')->count(),
            'inactive_users' => $users->where('status', 'inactive')->count(),
            'admin_users' => $users->where('role', 'admin')->count(),
            'leader_users' => $users->where('role', 'leader')->count(),
            'regular_users' => $users->where('role', 'user')->count(),
        ];
        
        // Get recent activities
        $recentUsers = User::latest()->take(10)->get();
        
        return view('admin.users.management', compact('users', 'stats', 'recentUsers'));
    }

    /**
     * Display user management page
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Display user management page (alias)
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $projects = Project::where('created_by', $user->user_id)->get();
        $memberships = ProjectMember::with('project')->where('user_id', $user->user_id)->get();
        $timeLogs = TimeLog::where('user_id', $user->user_id)->sum('hours_logged');

        return view('admin.users.show', compact('user', 'projects', 'memberships', 'timeLogs'));
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Show form to create new user (alias)
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'full_name' => 'required|string|max:100',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,leader,user',
        ]);

        if ($validator->fails()) {
            // Check if request expects JSON response
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'full_name' => $request->full_name,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'active', // Default status
            ]);

            // Check if request expects JSON response
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'message' => 'User berhasil dibuat!',
                    'user' => $user
                ], 201);
            }

            return redirect()->route('admin.users')->with('success', 'User berhasil dibuat!');
            
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            
            // Check if request expects JSON response
            if ($request->expectsJson() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'error' => 'An error occurred while creating user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat user')
                ->withInput();
        }
    }

    /**
     * Show form to edit user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Show form to edit user (alias)
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'full_name' => 'required|string|max:100',
            'role' => 'required|in:admin,leader,user',
            'status' => 'required|in:active,inactive',
        ]);

        // If password is provided, validate and hash it
        if ($request->filled('password')) {
            $validator->addRules([
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'email' => $request->email,
            'full_name' => $request->full_name,
            'role' => $request->role,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Check if user has any active projects or assignments
        $hasActiveProjects = Project::where('created_by', $user->user_id)->exists();
        $hasProjectMemberships = ProjectMember::where('user_id', $user->user_id)->exists();

        if ($hasActiveProjects || $hasProjectMemberships) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user yang masih terkait dengan project!');
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Delete user (alias)
     */
    public function deleteUser(User $user)
    {
        // Check if user has any active projects or assignments
        $hasActiveProjects = Project::where('created_by', $user->user_id)->exists();
        $hasProjectMemberships = ProjectMember::where('user_id', $user->user_id)->exists();

        if ($hasActiveProjects || $hasProjectMemberships) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user yang masih terkait dengan project!');
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Show user details (alias)
     */
    public function showUser(User $user)
    {
        $projects = Project::where('created_by', $user->user_id)->get();
        $memberships = ProjectMember::with('project')->where('user_id', $user->user_id)->get();
        $timeLogs = TimeLog::where('user_id', $user->user_id)->sum('hours_logged');

        return view('admin.users.show', compact('user', 'projects', 'memberships', 'timeLogs'));
    }

    /**
     * Display system reports
     */
    public function reports()
    {
        $userStats = [
            'admin_count' => User::where('role', 'admin')->count(),
            'leader_count' => User::where('role', 'leader')->count(),
            'user_count' => User::where('role', 'user')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
        ];

        $projectStats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'on_hold_projects' => Project::where('status', 'on_hold')->count(),
        ];

        $taskStats = [
            'total_tasks' => Card::count(),
            'todo_tasks' => Card::where('status', 'todo')->count(),
            'in_progress_tasks' => Card::where('status', 'in_progress')->count(),
            'completed_tasks' => Card::where('status', 'completed')->count(),
        ];

        $recentActivity = [
            'recent_projects' => Project::with('creator')->latest()->take(10)->get(),
            'recent_users' => User::latest()->take(10)->get(),
            'total_time_logged' => TimeLog::sum('hours_logged'),
        ];

        return view('admin.reports', compact('userStats', 'projectStats', 'taskStats', 'recentActivity'));
    }

    /**
     * Deactivate a user
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($user->role === 'admin') {
            return response()->json(['error' => 'Cannot deactivate admin users.'], 422);
        }

        $user->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'User berhasil dinonaktifkan.',
            'user' => $user
        ]);
    }

    /**
     * Deactivate a user (alias)
     */
    public function deactivateUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($user->role === 'admin') {
            return response()->json(['error' => 'Cannot deactivate admin users.'], 422);
        }

        $user->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'User berhasil dinonaktifkan.',
            'user' => $user
        ]);
    }

    /**
     * Activate a user
     */
    public function activate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'User berhasil diaktifkan.',
            'user' => $user
        ]);
    }

    /**
     * Activate a user (alias)
     */
    public function activateUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'User berhasil diaktifkan.',
            'user' => $user
        ]);
    }

    /**
     * Display system settings
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        // Implementation for system settings update
        // This could include app configurations, email settings, etc.
        
        return redirect()->back()->with('success', 'Settings berhasil diupdate!');
    }
}