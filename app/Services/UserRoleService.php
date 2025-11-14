<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserRoleService
{
    private $user;
    private $cacheKey;
    private $cacheTTL = 300; // 5 minutes

    public function __construct()
    {
        $this->user = Auth::user();
        $this->cacheKey = "user_roles_{$this->user->user_id}";
    }

    /**
     * Get comprehensive user role data with caching
     */
    public function getUserRoleData()
    {
        return Cache::remember($this->cacheKey, $this->cacheTTL, function () {
            // Single query to get all project roles for the user
            $projectRoles = DB::table('project_members')
                ->select('role', 'project_id', 'created_at')
                ->where('user_id', $this->user->user_id)
                ->get()
                ->groupBy('role');

            // Calculate role counts
            $roleCounts = [
                'project_manager' => $projectRoles->get('project_manager', collect())->count(),
                'developer' => $projectRoles->get('developer', collect())->count(),
                'designer' => $projectRoles->get('designer', collect())->count(),
            ];

            // Get active projects for each role
            $roleProjects = [
                'project_manager' => $projectRoles->get('project_manager', collect())->pluck('project_id')->toArray(),
                'developer' => $projectRoles->get('developer', collect())->pluck('project_id')->toArray(),
                'designer' => $projectRoles->get('designer', collect())->pluck('project_id')->toArray(),
            ];

            // Determine primary role and display
            $primaryRole = $this->determinePrimaryRole($roleCounts);
            $roleDisplay = $this->getRoleDisplay($primaryRole, $roleCounts);
            
            // Get permissions based on roles
            $permissions = $this->calculatePermissions($roleCounts);

            return [
                'user_role' => $this->user->role,
                'project_roles' => $projectRoles->toArray(),
                'role_counts' => $roleCounts,
                'role_projects' => $roleProjects,
                'primary_role' => $primaryRole,
                'role_display' => $roleDisplay['display'],
                'role_color' => $roleDisplay['color'],
                'role_icon' => $roleDisplay['icon'],
                'permissions' => $permissions,
                'has_multiple_roles' => array_sum(array_filter($roleCounts)) > 1,
                'total_projects' => count(array_unique(array_merge(...array_values($roleProjects)))),
            ];
        });
    }

    /**
     * Determine primary role based on hierarchy and counts
     */
    private function determinePrimaryRole($roleCounts)
    {
        // System roles take precedence
        if ($this->user->role === 'admin') return 'admin';
        if ($this->user->role === 'leader') return 'leader';

        // Project roles hierarchy: PM > Designer+Developer > Designer > Developer
        if ($roleCounts['project_manager'] > 0) return 'project_manager';
        if ($roleCounts['designer'] > 0 && $roleCounts['developer'] > 0) return 'hybrid';
        if ($roleCounts['designer'] > 0) return 'designer';
        if ($roleCounts['developer'] > 0) return 'developer';

        return 'member'; // Default team member
    }

    /**
     * Get role display configuration
     */
    private function getRoleDisplay($primaryRole, $roleCounts)
    {
        $roleConfigs = [
            'admin' => [
                'display' => '👑 System Administrator',
                'color' => 'bg-red-100 text-red-800 border-red-200',
                'icon' => '👑'
            ],
            'leader' => [
                'display' => '👨‍💼 Team Leader',
                'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'icon' => '👨‍💼'
            ],
            'project_manager' => [
                'display' => '🏆 Project Manager',
                'color' => 'bg-purple-100 text-purple-800 border-purple-200',
                'icon' => '🏆'
            ],
            'hybrid' => [
                'display' => '🎨💻 Multi-Role Developer',
                'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'icon' => '🎯'
            ],
            'designer' => [
                'display' => '🎨 UI/UX Designer',
                'color' => 'bg-pink-100 text-pink-800 border-pink-200',
                'icon' => '🎨'
            ],
            'developer' => [
                'display' => '💻 Developer',
                'color' => 'bg-green-100 text-green-800 border-green-200',
                'icon' => '💻'
            ],
            'member' => [
                'display' => '👤 Team Member',
                'color' => 'bg-blue-100 text-blue-800 border-blue-200',
                'icon' => '👤'
            ]
        ];

        return $roleConfigs[$primaryRole] ?? $roleConfigs['member'];
    }

    /**
     * Calculate user permissions based on roles
     */
    private function calculatePermissions($roleCounts)
    {
        return [
            // System level permissions
            'can_create_projects' => $this->user->role === 'admin',
            'can_manage_users' => $this->user->role === 'admin',
            'can_access_system_reports' => in_array($this->user->role, ['admin', 'leader']),
            'can_manage_global_settings' => $this->user->role === 'admin',
            
            // Project level permissions
            'can_manage_project_members' => $roleCounts['project_manager'] > 0 || $this->user->role === 'admin',
            'can_create_tasks' => $roleCounts['project_manager'] > 0 || $this->user->role === 'leader',
            'can_assign_tasks' => $roleCounts['project_manager'] > 0 || $this->user->role === 'leader',
            'can_view_project_reports' => $roleCounts['project_manager'] > 0 || $this->user->role === 'leader',
            
            // Task level permissions
            'can_update_any_task' => $this->user->role === 'admin' || $roleCounts['project_manager'] > 0,
            'can_comment_on_tasks' => true, // All users can comment on their assigned tasks
            'can_create_subtasks' => $roleCounts['project_manager'] > 0 || array_sum($roleCounts) > 0,
            
            // Role specific permissions
            'has_developer_access' => $roleCounts['developer'] > 0,
            'has_designer_access' => $roleCounts['designer'] > 0,
            'has_pm_access' => $roleCounts['project_manager'] > 0,
        ];
    }

    /**
     * Get menu items based on user role and permissions
     */
    public function getMenuItems()
    {
        $roleData = $this->getUserRoleData();
        $permissions = $roleData['permissions'];
        
        $menuItems = [];

        // Dashboard (always visible)
        $menuItems[] = [
            'type' => 'single',
            'title' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'permission' => true
        ];

        // Admin Menu
        if ($this->user->role === 'admin') {
            $menuItems[] = [
                'type' => 'dropdown',
                'title' => 'System Management',
                'icon' => 'settings',
                'badge' => '👑 ADMIN',
                'badge_color' => 'bg-red-100 text-red-800',
                'items' => [
                    [
                        'title' => 'All Projects',
                        'url' => route('projects.index'),
                        'icon' => 'dot',
                        'color' => 'blue'
                    ],
                    [
                        'title' => 'Create Project',
                        'url' => route('projects.create'),
                        'icon' => 'dot',
                        'color' => 'green'
                    ],
                    [
                        'title' => 'User Management',
                        'url' => '#',
                        'icon' => 'dot',
                        'color' => 'yellow'
                    ],
                    [
                        'title' => 'System Reports',
                        'url' => '#',
                        'icon' => 'dot',
                        'color' => 'purple'
                    ]
                ]
            ];
        }

        // Project Management (for leaders and PMs)
        if ($permissions['can_manage_project_members'] || $this->user->role === 'leader') {
            $menuItems[] = [
                'type' => 'dropdown',
                'title' => 'Project Management',
                'icon' => 'project',
                'items' => $this->getProjectManagementItems($roleData)
            ];
        }

        // Task Management (universal)
        $menuItems[] = [
            'type' => 'dropdown',
            'title' => 'My Tasks',
            'icon' => 'tasks',
            'badge' => $this->getTasksBadge(),
            'items' => $this->getTaskMenuItems($roleData)
        ];

        // Role-specific menus
        if ($roleData['has_multiple_roles']) {
            $menuItems = array_merge($menuItems, $this->getRoleSpecificMenus($roleData));
        }

        // Common features
        $menuItems[] = [
            'type' => 'single',
            'title' => 'Calendar',
            'icon' => 'calendar',
            'url' => route('calendar'),
            'permission' => true
        ];

        $menuItems[] = [
            'type' => 'single',
            'title' => 'Notifications',
            'icon' => 'notifications',
            'url' => route('notifications.index'),
            'badge' => $this->getNotificationsBadge(),
            'permission' => true
        ];

        return $menuItems;
    }

    /**
     * Get project management menu items based on user's roles
     */
    private function getProjectManagementItems($roleData)
    {
        $items = [];

        if ($roleData['role_counts']['project_manager'] > 0) {
            $items[] = [
                'title' => 'My Managed Projects',
                'url' => route('projects.index', ['filter' => 'managed']),
                'icon' => 'dot',
                'color' => 'purple'
            ];
            
            $items[] = [
                'title' => 'Team Management',
                'url' => '#',
                'icon' => 'dot',
                'color' => 'blue'
            ];
        }

        if ($this->user->role === 'leader') {
            $items[] = [
                'title' => 'Assigned Projects',
                'url' => route('projects.index', ['filter' => 'assigned']),
                'icon' => 'dot',
                'color' => 'green'
            ];
            
            $items[] = [
                'title' => 'Team Performance',
                'url' => '#',
                'icon' => 'dot',
                'color' => 'yellow'
            ];
        }

        return $items;
    }

    /**
     * Get task menu items based on roles
     */
    private function getTaskMenuItems($roleData)
    {
        $items = [
            [
                'title' => 'All My Tasks',
                'url' => route('tasks.my'),
                'icon' => 'dot',
                'color' => 'blue'
            ],
            [
                'title' => 'In Progress',
                'url' => route('tasks.my', ['filter' => 'in_progress']),
                'icon' => 'dot',
                'color' => 'yellow'
            ],
            [
                'title' => 'To Do',
                'url' => route('tasks.my', ['filter' => 'todo']),
                'icon' => 'dot',
                'color' => 'gray'
            ]
        ];

        // Add role-specific task filters
        if ($roleData['role_counts']['designer'] > 0) {
            $items[] = [
                'title' => 'Design Tasks',
                'url' => route('tasks.my', ['role_filter' => 'designer']),
                'icon' => 'dot',
                'color' => 'pink'
            ];
        }

        if ($roleData['role_counts']['developer'] > 0) {
            $items[] = [
                'title' => 'Development Tasks',
                'url' => route('tasks.my', ['role_filter' => 'developer']),
                'icon' => 'dot',
                'color' => 'green'
            ];
        }

        return $items;
    }

    /**
     * Get role-specific menu sections
     */
    private function getRoleSpecificMenus($roleData)
    {
        $menus = [];

        // Add context switching menu for multi-role users
        $menus[] = [
            'type' => 'dropdown',
            'title' => 'Switch Context',
            'icon' => 'switch',
            'badge' => '🎯 MULTI-ROLE',
            'badge_color' => 'bg-indigo-100 text-indigo-800',
            'items' => $this->getContextSwitchItems($roleData)
        ];

        return $menus;
    }

    /**
     * Get context switching items for multi-role users
     */
    private function getContextSwitchItems($roleData)
    {
        $items = [];

        foreach ($roleData['role_counts'] as $role => $count) {
            if ($count > 0) {
                $roleInfo = $this->getRoleInfo($role);
                $items[] = [
                    'title' => $roleInfo['title'] . " ({$count} projects)",
                    'url' => route('tasks.my', ['role_filter' => $role]),
                    'icon' => 'dot',
                    'color' => $roleInfo['color']
                ];
            }
        }

        return $items;
    }

    /**
     * Get role information
     */
    private function getRoleInfo($role)
    {
        $roleInfos = [
            'project_manager' => ['title' => '🏆 Project Manager View', 'color' => 'purple'],
            'developer' => ['title' => '💻 Developer View', 'color' => 'green'],
            'designer' => ['title' => '🎨 Designer View', 'color' => 'pink']
        ];

        return $roleInfos[$role] ?? ['title' => 'Unknown', 'color' => 'gray'];
    }

    /**
     * Get tasks badge (e.g., pending tasks count)
     */
    private function getTasksBadge()
    {
        // This would typically come from a cached count
        return Cache::remember("user_tasks_badge_{$this->user->user_id}", 60, function() {
            $pendingTasks = DB::table('cards')
                ->join('card_assignments', 'cards.card_id', '=', 'card_assignments.card_id')
                ->where('card_assignments.user_id', $this->user->user_id)
                ->whereIn('cards.status', ['todo', 'in_progress'])
                ->count();
                
            return $pendingTasks > 0 ? $pendingTasks : null;
        });
    }

    /**
     * Get notifications badge
     */
    private function getNotificationsBadge()
    {
        return Cache::remember("user_notifications_badge_{$this->user->user_id}", 60, function() {
            $unreadCount = DB::table('notifications')
                ->where('user_id', $this->user->user_id)
                ->whereNull('read_at')
                ->count();
                
            return $unreadCount > 0 ? $unreadCount : null;
        });
    }

    /**
     * Clear user role cache (call this when user roles change)
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
        Cache::forget("user_tasks_badge_{$this->user->user_id}");
        Cache::forget("user_notifications_badge_{$this->user->user_id}");
    }
}