<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\LeaderController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SubtaskTimerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes are organized by access level and functionality:
| 1. Guest Routes (Authentication)
| 2. Admin Routes (Full System Access)
| 3. Leader Routes (Project Management)
| 4. User Routes (Task Participation)
| 5. Common Routes (All Authenticated Users)
|
*/

/*
|--------------------------------------------------------------------------
| 1. GUEST ROUTES - Authentication
|--------------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {
    // Login & Registration
    Route::get('/', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Social Authentication
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('social.callback');
});

//ini auth bro
Route::middleware(['auth'])->group(function () {
    
    // Logout & Social Account Management
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::delete('/auth/{provider}/unlink', [SocialiteController::class, 'unlinkProvider'])->name('social.unlink');
    
    // Universal Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // My Tasks (accessible by all authenticated users)
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my');
    Route::get('/api/my-active-task', [TaskController::class, 'getUserActiveTask'])->name('tasks.active');
    
    // Task Management Routes (accessible by leaders and members)
    Route::prefix('admin/projects/{project}/tasks')->name('admin.projects.tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/{task}/status', [TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{task}/update-status', [TaskController::class, 'updateStatus'])->name('update-status');
    });
    
    // Subtask Management Routes (nested under tasks)
    Route::prefix('tasks/{task}/subtasks')->name('tasks.subtasks.')->group(function () {
        Route::post('/', [\App\Http\Controllers\SubtaskController::class, 'store'])->name('store');
        Route::put('/{subtask}', [\App\Http\Controllers\SubtaskController::class, 'update'])->name('update');
        Route::post('/{subtask}/toggle', [\App\Http\Controllers\SubtaskController::class, 'toggleComplete'])->name('toggle');
        Route::delete('/{subtask}', [\App\Http\Controllers\SubtaskController::class, 'destroy'])->name('destroy');
        
        // Subtask Timer Routes
        Route::post('/{subtask}/start-timer', [SubtaskTimerController::class, 'startTimer'])->name('start-timer');
        Route::post('/{subtask}/stop-timer', [SubtaskTimerController::class, 'stopTimer'])->name('stop-timer');
    });
    
    // TimeLog Management Routes (timer for tasks)
    Route::prefix('tasks/{task}/timer')->name('tasks.timer.')->group(function () {
        Route::post('/start', [\App\Http\Controllers\TimeLogController::class, 'start'])->name('start');
        Route::post('/stop', [\App\Http\Controllers\TimeLogController::class, 'stop'])->name('stop');
        Route::get('/status', [\App\Http\Controllers\TimeLogController::class, 'status'])->name('status');
        Route::get('/history', [\App\Http\Controllers\TimeLogController::class, 'history'])->name('history');
    });

    // Board Transition Routes
    Route::prefix('tasks/{task}/transitions')->name('tasks.transitions.')->group(function () {
        Route::post('/complete', [\App\Http\Controllers\BoardTransitionController::class, 'markComplete'])->name('complete');
        Route::post('/approve', [\App\Http\Controllers\BoardTransitionController::class, 'approve'])->name('approve');
        Route::post('/reject', [\App\Http\Controllers\BoardTransitionController::class, 'reject'])->name('reject');
        Route::post('/change-status', [\App\Http\Controllers\BoardTransitionController::class, 'changeStatus'])->name('change-status');
        Route::get('/available', [\App\Http\Controllers\BoardTransitionController::class, 'getAvailableTransitions'])->name('available');
    });

        // Comment Routes - Projects
    Route::prefix('projects/{project}/comments')->name('projects.comments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProjectCommentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ProjectCommentController::class, 'store'])->name('store');
        Route::delete('/{comment}', [\App\Http\Controllers\ProjectCommentController::class, 'destroy'])->name('destroy');
    });

    // Comment Routes - Tasks
    Route::prefix('tasks/{task}/comments')->name('tasks.comments.')->group(function () {
        Route::post('/', [\App\Http\Controllers\TaskCommentController::class, 'store'])->name('store');
        Route::delete('/{comment}', [\App\Http\Controllers\TaskCommentController::class, 'destroy'])->name('destroy');
    });

    // Comment Routes - Cards

    // Comment Routes - Cards
    Route::prefix('cards/{card}/comments')->name('cards.comments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CardCommentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\CardCommentController::class, 'store'])->name('store');
        Route::delete('/{comment}', [\App\Http\Controllers\CardCommentController::class, 'destroy'])->name('destroy');
    });

    // Leaderboard Routes
    Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/api/leaderboard/widget', [\App\Http\Controllers\LeaderboardController::class, 'widget'])->name('leaderboard.widget');
    
    //Route Admin Fitur
    Route::middleware(['role.admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // === DASHBOARD & OVERVIEW ===
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/enhanced-dashboard', function() {
            return view('admin.enhanced-dashboard');
        })->name('enhanced-dashboard');
        
        // === PROJECT MANAGEMENT ===
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
            
            // Project Administration
            Route::prefix('admin')->name('admin.')->group(function () {
                Route::get('/', [\App\Http\Controllers\ProjectAdminController::class, 'index'])->name('index');
                Route::get('/manage-projects', [\App\Http\Controllers\ProjectAdminController::class, 'manageProjects'])->name('manage-projects');
                Route::get('/manage-team-members', [\App\Http\Controllers\ProjectAdminController::class, 'manageTeamMembers'])->name('manage-team-members');
                Route::get('/manage-data-access', [\App\Http\Controllers\ProjectAdminController::class, 'manageDataAccess'])->name('manage-data-access');
                Route::get('/manage-tasks', [\App\Http\Controllers\ProjectAdminController::class, 'manageTasks'])->name('manage-tasks');
                
                // Project Admin API
                Route::post('/create-project', [\App\Http\Controllers\ProjectAdminController::class, 'createProject'])->name('create-project');
                Route::delete('/delete-project/{project}', [\App\Http\Controllers\ProjectAdminController::class, 'deleteProject'])->name('delete-project');
                Route::post('/add-member', [\App\Http\Controllers\ProjectAdminController::class, 'addMemberToProject'])->name('add-member');
                Route::post('/remove-member', [\App\Http\Controllers\ProjectAdminController::class, 'removeMemberFromProject'])->name('remove-member');
                Route::put('/update-status/{project}', [\App\Http\Controllers\ProjectAdminController::class, 'updateProjectStatus'])->name('update-status');
                Route::put('/change-leader/{project}', [\App\Http\Controllers\ProjectAdminController::class, 'changeProjectLeader'])->name('change-leader');
                Route::get('/analytics/{project}', [\App\Http\Controllers\ProjectAdminController::class, 'getProjectAnalytics'])->name('analytics');
            });
        });
        
        //USER MANAGEMENT
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');
            Route::get('/management', [\App\Http\Controllers\Admin\AdminController::class, 'management'])->name('management');
            Route::get('/create', [\App\Http\Controllers\Admin\AdminController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('store');
            Route::get('/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'edit'])->name('edit');
            Route::put('/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'destroy'])->name('destroy');
            
            // User Status Management
            Route::post('/deactivate', [\App\Http\Controllers\Admin\AdminController::class, 'deactivate'])->name('deactivate');
            Route::post('/activate', [\App\Http\Controllers\Admin\AdminController::class, 'activate'])->name('activate');
        });
        
        // === LEADER MANAGEMENT ===
        Route::prefix('leaders')->name('leaders.')->group(function () {
            Route::get('/management', [LeaderController::class, 'management'])->name('management');
            Route::get('/available', [LeaderController::class, 'getAvailableLeaders'])->name('available');
            Route::get('/search', [LeaderController::class, 'searchLeaders'])->name('search');
            Route::post('/assign', [LeaderController::class, 'assignToProject'])->name('assign');
            Route::post('/promote', [LeaderController::class, 'promoteToLeader'])->name('promote');
            Route::post('/demote', [LeaderController::class, 'removeLeaderRole'])->name('demote');
        });
        
        // === REPORTS & ANALYTICS ===
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('generate');
            
            // Excel Export Routes
            Route::get('/export/projects', [\App\Http\Controllers\Admin\ReportController::class, 'exportProjects'])->name('export.projects');
            Route::get('/export/tasks', [\App\Http\Controllers\Admin\ReportController::class, 'exportTasks'])->name('export.tasks');
            Route::get('/export/users', [\App\Http\Controllers\Admin\ReportController::class, 'exportUsers'])->name('export.users');
            Route::get('/export/comprehensive', [\App\Http\Controllers\Admin\ReportController::class, 'exportComprehensive'])->name('export.comprehensive');
            
            // Old analytics routes (if needed)
            Route::get('/analytics', [ReportsController::class, 'index'])->name('analytics');
            Route::get('/management', [ReportsController::class, 'management'])->name('management');
            Route::get('/projects', [ReportsController::class, 'projects'])->name('projects');
            Route::get('/users', [ReportsController::class, 'users'])->name('users');
            Route::get('/time-tracking', [ReportsController::class, 'timeTracking'])->name('time-tracking');
            Route::get('/performance', [ReportsController::class, 'performance'])->name('performance');
            Route::get('/export', [ReportsController::class, 'export'])->name('export-json');
            
            // Advanced Reports
            Route::prefix('advanced')->name('advanced.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'index'])->name('index');
                Route::post('/generate', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'generateReport'])->name('generate');
                Route::post('/export', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'exportReport'])->name('export');
                Route::get('/templates', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'getReportTemplates'])->name('templates');
            });
        });
        
        // === SYSTEM MONITORING ===
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/system-health', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'getSystemHealth'])->name('system-health');
            Route::get('/project-statistics', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'getProjectStatistics'])->name('project-statistics');
            Route::get('/performance-metrics', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'getPerformanceMetrics'])->name('performance-metrics');
            Route::get('/security-metrics', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'getSecurityMetrics'])->name('security-metrics');
            Route::get('/system-alerts', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'getSystemAlerts'])->name('system-alerts');
            Route::post('/clear-alerts', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'clearAlerts'])->name('clear-alerts');
        });
        
        // === USER ACTIVITY TRACKING ===
        Route::prefix('activity')->name('activity.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [\App\Http\Controllers\Admin\UserActivityController::class, 'getUserActivity'])->name('user');
            Route::get('/analytics', [\App\Http\Controllers\Admin\UserActivityController::class, 'getActivityAnalytics'])->name('analytics');
            Route::post('/log', [\App\Http\Controllers\Admin\UserActivityController::class, 'logActivity'])->name('log');
            Route::get('/audit-trail', [\App\Http\Controllers\Admin\UserActivityController::class, 'getAuditTrail'])->name('audit-trail');
            Route::post('/export', [\App\Http\Controllers\Admin\UserActivityController::class, 'exportActivity'])->name('export');
            Route::get('/security-alerts', [\App\Http\Controllers\Admin\UserActivityController::class, 'getSecurityAlerts'])->name('security-alerts');
            Route::post('/acknowledge-alert', [\App\Http\Controllers\Admin\UserActivityController::class, 'acknowledgeAlert'])->name('acknowledge-alert');
        });
        
        // === SYSTEM SETTINGS ===
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('index');
            Route::get('/management', [\App\Http\Controllers\SettingsController::class, 'management'])->name('management');
            Route::post('/', [\App\Http\Controllers\Admin\AdminController::class, 'updateSettings'])->name('update');
            
            // Settings API
            Route::post('/general', [\App\Http\Controllers\SettingsController::class, 'generalSettings'])->name('update.general');
            Route::post('/email', [\App\Http\Controllers\SettingsController::class, 'emailSettings'])->name('update.email');
            Route::post('/test-email', [\App\Http\Controllers\SettingsController::class, 'testEmail'])->name('test.email');
            Route::post('/backup', [\App\Http\Controllers\SettingsController::class, 'createBackup'])->name('backup');
            Route::post('/clear-cache', [\App\Http\Controllers\SettingsController::class, 'clearCache'])->name('clear.cache');
            Route::post('/security', [\App\Http\Controllers\SettingsController::class, 'securitySettings'])->name('update.security');
            Route::get('/system-info', [\App\Http\Controllers\SettingsController::class, 'getSystemInfo'])->name('system.info');
        });
        
        // === SYSTEM UTILITIES ===
        Route::prefix('utilities')->name('utilities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'index'])->name('index');
            Route::get('/bulk-operations', function() {
                return view('admin.bulk-operations');
            })->name('bulk-operations');
            
            // Backup Management
            Route::prefix('backup')->name('backup.')->group(function () {
                Route::post('/', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'createBackup'])->name('create');
                Route::get('/', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'listBackups'])->name('list');
                Route::get('/{filename}/download', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'downloadBackup'])->name('download');
                Route::delete('/{filename}', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'deleteBackup'])->name('delete');
            });
            
            // System Maintenance
            Route::post('/maintenance', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'systemMaintenance'])->name('maintenance');
            Route::get('/system-info', [\App\Http\Controllers\Admin\AdminUtilitiesController::class, 'getSystemInfo'])->name('system-info');
        });
        
        // === BULK OPERATIONS ===
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('/update-project-status', [\App\Http\Controllers\ProjectAdminController::class, 'bulkUpdateProjectStatus'])->name('update-project-status');
            Route::post('/assign-users', [\App\Http\Controllers\ProjectAdminController::class, 'bulkAssignUsers'])->name('assign-users');
            Route::post('/remove-users', [\App\Http\Controllers\ProjectAdminController::class, 'bulkRemoveUsers'])->name('remove-users');
            Route::post('/update-task-status', [\App\Http\Controllers\ProjectAdminController::class, 'bulkUpdateTaskStatus'])->name('update-task-status');
            Route::post('/update-task-priority', [\App\Http\Controllers\ProjectAdminController::class, 'bulkUpdateTaskPriority'])->name('update-task-priority');
            Route::delete('/delete-tasks', [\App\Http\Controllers\ProjectAdminController::class, 'bulkDeleteTasks'])->name('delete-tasks');
            Route::get('/export-project-data', [\App\Http\Controllers\ProjectAdminController::class, 'exportProjectData'])->name('export-project-data');
            Route::get('/operation-progress/{operationId}', [\App\Http\Controllers\ProjectAdminController::class, 'getBulkOperationProgress'])->name('operation-progress');
        });
        
        // === TASK MANAGEMENT (Admin) ===
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/statistics', [\App\Http\Controllers\ProjectAdminController::class, 'getTaskStatistics'])->name('statistics');
            Route::get('/projects-list', [\App\Http\Controllers\ProjectAdminController::class, 'getProjectsList'])->name('projects-list');
            Route::get('/', [\App\Http\Controllers\ProjectAdminController::class, 'getTasks'])->name('list');
            Route::get('/{task}', [\App\Http\Controllers\ProjectAdminController::class, 'getTaskDetails'])->name('details');
            Route::put('/{task}', [\App\Http\Controllers\ProjectAdminController::class, 'updateTask'])->name('update');
            Route::put('/{task}/reassign', [\App\Http\Controllers\ProjectAdminController::class, 'reassignTask'])->name('reassign');
        });
    });
    
 //Route Leader
    
    Route::middleware(['role.leader'])->prefix('leader')->group(function () {
        
        // === LEADER DASHBOARD - Simplified for 4 core functions ===
        Route::get('/dashboard', [\App\Http\Controllers\LeaderDashboardController::class, 'index'])->name('leader.dashboard');
        Route::get('/dashboard-data', [\App\Http\Controllers\LeaderDashboardController::class, 'getDashboardData'])->name('leader.dashboard.data');
        Route::get('/projects/{project}/team-members', [\App\Http\Controllers\LeaderDashboardController::class, 'getProjectTeamMembers'])->name('leader.project.team-members');
        Route::post('/quick-assign-task', [\App\Http\Controllers\LeaderDashboardController::class, 'quickAssignTask'])->name('leader.quick-assign-task');
        Route::put('/tasks/{task}/update-priority', [\App\Http\Controllers\LeaderDashboardController::class, 'updateTaskPriority'])->name('leader.tasks.update-priority');
        Route::put('/tasks/{task}/update-status', [\App\Http\Controllers\LeaderDashboardController::class, 'updateTaskStatus'])->name('leader.tasks.update-status');
        
        // === PROJECT LEADERSHIP ===
        Route::get('/projects', [\App\Http\Controllers\ProjectLeaderController::class, 'myProjects'])->name('leader.projects');
        // Note: Project creation is admin-only. Leaders can only view and manage assigned projects.
        Route::get('/projects/{project}', [\App\Http\Controllers\ProjectLeaderController::class, 'show'])->name('leader.projects.show');
        // Note: Project editing is admin-only. Leaders manage tasks, not project settings.
        Route::get('/projects/{project}/team', [\App\Http\Controllers\ProjectLeaderController::class, 'manageTeam'])->name('leader.manage-team');
        Route::get('/projects/{project}/stats', [\App\Http\Controllers\ProjectLeaderController::class, 'getProjectStats'])->name('leader.project-stats');
        Route::get('/projects/{project}/search-users', [\App\Http\Controllers\ProjectLeaderController::class, 'searchAvailableUsers'])->name('leader.search-users');
        
        // Team Management
        Route::post('/projects/{project}/add-member', [\App\Http\Controllers\ProjectLeaderController::class, 'addTeamMember'])->name('leader.add-member');
        Route::delete('/projects/{project}/remove-member', [\App\Http\Controllers\ProjectLeaderController::class, 'removeTeamMember'])->name('leader.remove-member');
        Route::put('/projects/{project}/update-role', [\App\Http\Controllers\ProjectLeaderController::class, 'updateMemberRole'])->name('leader.update-role');
        
        // === BOARD MANAGEMENT ===
        Route::resource('boards', BoardController::class)->names([
            'index' => 'leader.boards.index',
            'create' => 'leader.boards.create',
            'store' => 'leader.boards.store',
            'show' => 'leader.boards.show',
            'edit' => 'leader.boards.edit',
            'update' => 'leader.boards.update',
            'destroy' => 'leader.boards.destroy',
        ]);
        
        // === LEADER TASK MANAGEMENT ===
        Route::prefix('projects/{project}/tasks')->name('leader.tasks.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\LeaderTaskController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\LeaderTaskController::class, 'store'])->name('store');
            Route::get('/{task}', [\App\Http\Controllers\LeaderTaskController::class, 'show'])->name('show');
            Route::post('/{task}/update-priority-status', [\App\Http\Controllers\LeaderTaskController::class, 'updatePriorityAndStatus'])->name('update-priority-status');
            Route::post('/{task}/reassign', [\App\Http\Controllers\LeaderTaskController::class, 'reassignTask'])->name('reassign');
        });
        
        // Project progress and task listing
        Route::get('/projects/{project}/progress', [\App\Http\Controllers\LeaderTaskController::class, 'getProjectProgress'])->name('leader.project.progress');
        Route::get('/projects/{project}/tasks', [\App\Http\Controllers\LeaderTaskController::class, 'getProjectTasks'])->name('leader.project.tasks');
        
        // Complete project
        Route::post('/projects/{project}/complete', [\App\Http\Controllers\ProjectLeaderController::class, 'complete'])->name('leader.projects.complete');
        
        // === LEGACY TASK CREATION (Redirect to new system) ===
        Route::get('/cards/create', function () {
            return redirect()->route('leader.projects');
        })->name('cards.create');
        
        // === PROGRESS REPORTS ===
        Route::get('/reports', function () {
            return view('dashboard.index');
        })->name('leader.reports');
        
        Route::get('/progress/reports', function () {
            return view('dashboard.index');
        })->name('progress.reports');
    });
    
 //USER ROUTE
    
    Route::middleware(['role.user'])->prefix('user')->group(function () {
        
        // === ASSIGNED PROJECTS ===
        Route::get('/projects', function () {
            return view('dashboard.index');
        })->name('projects');
        
        Route::get('/projects/assigned', function () {
            return view('dashboard.index');
        })->name('projects.assigned');
        
        // === TASK MANAGEMENT ===
        Route::get('/cards', function () {
            return view('dashboard.index');
        })->name('cards.index');
        Route::get('/subtasks', function () {
            return view('dashboard.index');
        })->name('subtasks.index');
        
        // === TIME TRACKING ===
        Route::get('/time-logs', function () {
            return view('dashboard.index');
        })->name('timelogs.index');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 6. COMMON ROUTES - All Authenticated Users
    |--------------------------------------------------------------------------
    */
    
    // === PROJECT ACCESS (Role-based) ===
    Route::middleware(['project.access:view'])->group(function () {
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    });
    
    Route::middleware(['project.access:edit_project'])->group(function () {
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    });
    
    // === PROJECT MEMBER MANAGEMENT (Hanya Leader yang bisa mengelola) ===
    Route::middleware(['role.leader'])->prefix('projects/{project}/members')->name('projects.members.')->group(function () {
        Route::get('/', [ProjectMemberController::class, 'index'])->name('index');
        Route::get('/create', [ProjectMemberController::class, 'create'])->name('create');
        Route::post('/', [ProjectMemberController::class, 'store'])->name('store');
        Route::get('/search', [ProjectMemberController::class, 'searchUsers'])->name('search');
        Route::put('/{member}', [ProjectMemberController::class, 'update'])->name('update');
        Route::delete('/{member}', [ProjectMemberController::class, 'destroy'])->name('destroy');
        Route::get('/available', [ProjectMemberController::class, 'getAvailableUsers'])->name('available');
    });
    
    // === TASK MANAGEMENT (Universal) ===
    // Note: Task list is now under admin.projects.tasks.index
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/subtasks', [TaskController::class, 'getSubtasks'])->name('subtasks'); // New route for AJAX
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{task}/status-simple', [TaskController::class, 'updateTaskStatus'])->name('update-status-simple');
        Route::patch('/{task}/priority', [TaskController::class, 'updatePriority'])->name('update-priority');
        Route::post('/{task}/assign', [TaskController::class, 'assignMembers'])->name('assign-members');
        Route::post('/{task}/progress', [TaskController::class, 'addProgress'])->name('add-progress');
        Route::get('/role/{role}', [TaskController::class, 'roleDashboard'])->name('role-dashboard');
    });
    
    // === WORK SESSION / TIME TRACKING ROUTES ===
    Route::prefix('work-sessions')->name('work-sessions.')->group(function () {
        Route::post('/start', [\App\Http\Controllers\WorkSessionController::class, 'startWork'])->name('start');
        Route::post('/stop', [\App\Http\Controllers\WorkSessionController::class, 'stopWork'])->name('stop');
        Route::post('/resume', [\App\Http\Controllers\WorkSessionController::class, 'resumeWork'])->name('resume');
        Route::get('/active', [\App\Http\Controllers\WorkSessionController::class, 'getActiveSession'])->name('active');
        Route::get('/today-total', [\App\Http\Controllers\WorkSessionController::class, 'getTodayTotal'])->name('today-total');
        Route::get('/history', [\App\Http\Controllers\WorkSessionController::class, 'getHistory'])->name('history');
    });
    
    // === EXTENSION REQUEST ROUTES ===
    Route::prefix('extension-requests')->name('extension-requests.')->group(function () {
        // For Developers: Create extension request
        Route::post('/', [\App\Http\Controllers\ExtensionRequestController::class, 'store'])->name('store');
        
        // For Leaders/Admins: View and manage extension requests
        Route::get('/', [\App\Http\Controllers\ExtensionRequestController::class, 'index'])->name('index');
        Route::post('/{extensionRequest}/approve', [\App\Http\Controllers\ExtensionRequestController::class, 'approve'])->name('approve');
        Route::post('/{extensionRequest}/reject', [\App\Http\Controllers\ExtensionRequestController::class, 'reject'])->name('reject');
    });
    
    // === SHARED FEATURES ===
    Route::get('/calendar', function () {
        return view('dashboard.index');
    })->name('calendar');
    
    // Notifications Routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear-read', [\App\Http\Controllers\NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    
    Route::get('/profile', function () {
        return view('dashboard.index');
    })->name('profile');
    
    Route::get('/comments', function () {
        return view('dashboard.index');
    })->name('comments.index');
    
    /*
    |--------------------------------------------------------------------------
    | 7. API ROUTES (Internal)
    |--------------------------------------------------------------------------
    */
    
    // === PROJECT TEMPLATES ===
    Route::get('/api/templates', function() {
        return response()->json(\App\Models\ProjectTemplate::active()->get());
    })->name('templates.api');
    
    // === USER SEARCH ===
    Route::get('/api/users/search', function(\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        // HANYA menampilkan user dengan role 'user'
        // Admin dan Leader tidak ditampilkan
        $users = \App\Models\User::where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->where('status', 'active')
            ->where('role', 'user') // HANYA user biasa, tidak termasuk admin dan leader
            ->limit(10)
            ->get(['user_id', 'full_name', 'username', 'email', 'role']);

        return response()->json($users);
    })->name('users.search');
    
    /*
    |--------------------------------------------------------------------------
    | 8. LEGACY ROUTES (Backward Compatibility)
    |--------------------------------------------------------------------------
    */
    
    // Keep some legacy route names for backward compatibility - Available to Admin only  
    Route::middleware(['role.admin'])->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store'); 
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/admin/leaders/available', [LeaderController::class, 'getAvailableLeaders'])->name('leaders.available');
        Route::get('/admin/leaders/search', [LeaderController::class, 'searchLeaders'])->name('leaders.search');
        Route::get('/admin/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('admin.users');
        // Admin Reports - CSV Generation
        Route::get('/admin/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
        Route::post('/admin/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('admin.reports.generate');
        Route::post('/admin/reports/monthly', [\App\Http\Controllers\ReportController::class, 'generateMonthly'])->name('admin.reports.monthly');
        Route::post('/admin/reports/yearly', [\App\Http\Controllers\ReportController::class, 'generateYearly'])->name('admin.reports.yearly');
        Route::post('/admin/reports/project', [\App\Http\Controllers\ReportController::class, 'generateProject'])->name('admin.reports.project');
        
        Route::get('/admin/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings');
        Route::get('/users/management', [\App\Http\Controllers\Admin\AdminController::class, 'usersManagement'])->name('users.management');
    });
    
    Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users.index');
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports.index');
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('settings');
    
    /*
    |--------------------------------------------------------------------------
    | 9. DEVELOPER & DESIGNER ROUTES - Task Execution & Collaboration
    |--------------------------------------------------------------------------
    */
    
    // Developer Routes - For developers and designers working on tasks
    Route::middleware(['role:developer,designer'])->prefix('developer')->name('developer.')->group(function () {
        
        // Task Management
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\DeveloperTaskController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\DeveloperTaskController::class, 'show'])->name('show');
            
            // Progress Updates
            Route::post('/{id}/update-progress', [\App\Http\Controllers\DeveloperTaskController::class, 'updateProgress'])->name('update-progress');
            
            // Comments & Collaboration
            Route::post('/{id}/comments', [\App\Http\Controllers\DeveloperTaskController::class, 'addComment'])->name('add-comment');
            
            // File Uploads
            Route::post('/{id}/upload', [\App\Http\Controllers\DeveloperTaskController::class, 'uploadFile'])->name('upload-file');
            Route::delete('/{taskId}/attachments/{attachmentId}', [\App\Http\Controllers\DeveloperTaskController::class, 'deleteAttachment'])->name('delete-attachment');
            
            // Time Tracking
            Route::post('/{id}/start-timer', [\App\Http\Controllers\DeveloperTaskController::class, 'startTimer'])->name('start-timer');
            Route::post('/{id}/stop-timer', [\App\Http\Controllers\DeveloperTaskController::class, 'stopTimer'])->name('stop-timer');
        });
    });
    
    // Designer Routes - Alias to developer routes (same functionality, different UI context)
    Route::middleware(['role:designer'])->prefix('designer')->name('designer.')->group(function () {
        Route::get('/tasks', [\App\Http\Controllers\DeveloperTaskController::class, 'index'])->name('tasks.index');
    });
});