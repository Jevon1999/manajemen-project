<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SidebarController extends Controller
{
    /**
     * Get user's role and project information for sidebar display
     */
    public static function getUserRoleInfo()
    {
        $user = Auth::user();
        if (!$user) return null;

        return [
            'user_role' => $user->role ?? 'user',
            'user_name' => $user->full_name,
            'user_avatar' => $user->avatar,
            'project_roles' => self::getProjectRoles($user->user_id),
            'is_project_manager' => self::hasProjectManagerRole($user->user_id),
            'is_designer' => self::hasRole($user->user_id, 'designer'),
            'is_developer' => self::hasRole($user->user_id, 'developer'),
        ];
    }

    /**
     * Get all project roles for the user
     */
    private static function getProjectRoles($userId)
    {
        return DB::table('project_members')
            ->where('user_id', $userId)
            ->pluck('role')
            ->toArray();
    }

    /**
     * Check if user has project manager role in any project
     */
    private static function hasProjectManagerRole($userId)
    {
        return DB::table('project_members')
            ->where('user_id', $userId)
            ->where('role', 'project_manager')
            ->exists();
    }

    /**
     * Check if user has specific role in any project
     */
    private static function hasRole($userId, $role)
    {
        return DB::table('project_members')
            ->where('user_id', $userId)
            ->where('role', $role)
            ->exists();
    }

    /**
     * Get sidebar configuration based on user role
     */
    public static function getSidebarConfig()
    {
        $user = Auth::user();
        $userRole = $user->role ?? 'user';
        
        $configs = [
            'admin' => [
                'component' => 'components.sidebar.admin',
                'theme' => 'red',
                'title' => 'System Administration',
                'icon' => '⚙️'
            ],
            'team_lead' => [
                'component' => 'components.sidebar.leader',
                'theme' => 'indigo',
                'title' => 'Team Leadership',
                'icon' => '👥'
            ],
            'team-lead' => [
                'component' => 'components.sidebar.leader',
                'theme' => 'indigo',
                'title' => 'Team Leadership',
                'icon' => '👥'
            ],
            'user' => [
                'component' => 'components.sidebar.user',
                'theme' => 'blue',
                'title' => 'Team Member',
                'icon' => '👤'
            ]
        ];

        return $configs[$userRole] ?? $configs['user'];
    }

    /**
     * Get notification count for user
     */
    public static function getNotificationCount($userId)
    {
        // Implementasi logic notifikasi di sini
        // Untuk sementara return 0
        return 0;
    }
}