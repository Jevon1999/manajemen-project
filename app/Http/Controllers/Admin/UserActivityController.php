<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\User;

class UserActivityController extends Controller
{
    /**
     * Show user activity tracking dashboard
     */
    public function index()
    {
        $activitySummary = $this->getActivitySummary();
        $recentActivities = $this->getRecentActivities(20);
        $topActiveUsers = $this->getTopActiveUsers(10);
        
        return view('admin.activity.index', compact('activitySummary', 'recentActivities', 'topActiveUsers'));
    }

    /**
     * Get detailed user activity
     */
    public function getUserActivity(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $dateRange = $this->getDateRange($request->input('range', '30_days'));
            
            $activities = $this->getUserActivities($userId, $dateRange);
            $sessionData = $this->getUserSessionData($userId, $dateRange);
            $performanceMetrics = $this->getUserPerformanceMetrics($userId, $dateRange);
            $securityEvents = $this->getUserSecurityEvents($userId, $dateRange);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->user_id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'last_login' => $user->last_login_at
                ],
                'activities' => $activities,
                'sessions' => $sessionData,
                'performance' => $performanceMetrics,
                'security' => $securityEvents,
                'date_range' => $dateRange
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user activity data'
            ], 500);
        }
    }

    /**
     * Get system-wide activity analytics
     */
    public function getActivityAnalytics(Request $request)
    {
        try {
            $dateRange = $this->getDateRange($request->input('range', '30_days'));
            $analysisType = $request->input('type', 'overview');
            
            $analytics = [];
            
            switch ($analysisType) {
                case 'overview':
                    $analytics = $this->getOverviewAnalytics($dateRange);
                    break;
                case 'behavioral':
                    $analytics = $this->getBehavioralAnalytics($dateRange);
                    break;
                case 'security':
                    $analytics = $this->getSecurityAnalytics($dateRange);
                    break;
                case 'performance':
                    $analytics = $this->getPerformanceAnalytics($dateRange);
                    break;
                default:
                    $analytics = $this->getOverviewAnalytics($dateRange);
            }

            return response()->json([
                'success' => true,
                'analytics' => $analytics,
                'type' => $analysisType,
                'date_range' => $dateRange
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting activity analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get activity analytics'
            ], 500);
        }
    }

    /**
     * Log user activity
     */
    public function logActivity(Request $request)
    {
        try {
            $activityData = [
                'user_id' => auth()->id(),
                'action' => $request->input('action'),
                'resource' => $request->input('resource'),
                'resource_id' => $request->input('resource_id'),
                'description' => $request->input('description'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
                'timestamp' => now(),
                'metadata' => json_encode($request->input('metadata', []))
            ];

            // Store in cache for real-time tracking
            $cacheKey = "user_activity_" . auth()->id();
            $cachedActivities = Cache::get($cacheKey, []);
            array_unshift($cachedActivities, $activityData);
            $cachedActivities = array_slice($cachedActivities, 0, 100); // Keep last 100 activities
            Cache::put($cacheKey, $cachedActivities, 3600); // Cache for 1 hour

            // Store in database (you would create an activity_logs table)
            // DB::table('activity_logs')->insert($activityData);

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity'
            ], 500);
        }
    }

    /**
     * Get audit trail for specific resource
     */
    public function getAuditTrail(Request $request)
    {
        try {
            $resource = $request->input('resource');
            $resourceId = $request->input('resource_id');
            $dateRange = $this->getDateRange($request->input('range', '30_days'));
            
            $auditTrail = $this->getResourceAuditTrail($resource, $resourceId, $dateRange);
            
            return response()->json([
                'success' => true,
                'audit_trail' => $auditTrail,
                'resource' => $resource,
                'resource_id' => $resourceId,
                'date_range' => $dateRange
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting audit trail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get audit trail'
            ], 500);
        }
    }

    /**
     * Export activity data
     */
    public function exportActivity(Request $request)
    {
        try {
            $format = $request->input('format', 'csv');
            $dateRange = $this->getDateRange($request->input('range', '30_days'));
            $filters = $request->input('filters', []);
            
            $activities = $this->getFilteredActivities($dateRange, $filters);
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($activities);
                case 'excel':
                    return $this->exportToExcel($activities);
                case 'json':
                    return $this->exportToJson($activities);
                default:
                    return $this->exportToCsv($activities);
            }

        } catch (\Exception $e) {
            Log::error('Error exporting activity data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export activity data'
            ], 500);
        }
    }

    /**
     * Get security alerts
     */
    public function getSecurityAlerts(Request $request)
    {
        try {
            $severity = $request->input('severity', 'all');
            $dateRange = $this->getDateRange($request->input('range', '7_days'));
            
            $alerts = $this->generateSecurityAlerts($severity, $dateRange);
            
            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'severity' => $severity,
                'date_range' => $dateRange
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting security alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get security alerts'
            ], 500);
        }
    }

    /**
     * Acknowledge security alert
     */
    public function acknowledgeAlert(Request $request)
    {
        try {
            $alertId = $request->input('alert_id');
            $notes = $request->input('notes', '');
            
            // Log alert acknowledgment
            $this->logActivity(new Request([
                'action' => 'alert_acknowledged',
                'resource' => 'security_alert',
                'resource_id' => $alertId,
                'description' => "Security alert acknowledged: {$notes}",
                'metadata' => ['alert_id' => $alertId, 'notes' => $notes]
            ]));
            
            return response()->json([
                'success' => true,
                'message' => 'Alert acknowledged successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error acknowledging alert: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alert'
            ], 500);
        }
    }

    // Private helper methods

    private function getActivitySummary()
    {
        // This would normally query the activity_logs table
        return [
            'total_users_today' => 45,
            'total_activities_today' => 1234,
            'average_session_duration' => '24 minutes',
            'most_active_feature' => 'Project Management',
            'security_alerts' => 3,
            'failed_logins_today' => 12
        ];
    }

    private function getRecentActivities($limit = 20)
    {
        // Simulate recent activities data
        $activities = [];
        $actions = ['login', 'logout', 'create_project', 'update_task', 'delete_user', 'view_report'];
        $users = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Wilson'];
        
        for ($i = 0; $i < $limit; $i++) {
            $activities[] = [
                'id' => $i + 1,
                'user_name' => $users[array_rand($users)],
                'action' => $actions[array_rand($actions)],
                'resource' => 'project',
                'description' => 'User performed action on resource',
                'ip_address' => '192.168.1.' . rand(1, 254),
                'timestamp' => Carbon::now()->subMinutes(rand(1, 1440))->format('Y-m-d H:i:s'),
                'risk_level' => ['low', 'medium', 'high'][rand(0, 2)]
            ];
        }
        
        return $activities;
    }

    private function getTopActiveUsers($limit = 10)
    {
        // Simulate top active users data
        $users = [];
        $names = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Wilson', 
                 'Diana Prince', 'Peter Parker', 'Bruce Wayne', 'Clark Kent', 'Tony Stark'];
        
        for ($i = 0; $i < $limit; $i++) {
            $users[] = [
                'id' => $i + 1,
                'name' => $names[$i],
                'activity_count' => rand(50, 200),
                'last_activity' => Carbon::now()->subMinutes(rand(1, 1440))->format('Y-m-d H:i:s'),
                'risk_score' => rand(1, 100),
                'session_duration' => rand(10, 120) . ' minutes'
            ];
        }
        
        // Sort by activity count
        usort($users, function($a, $b) {
            return $b['activity_count'] - $a['activity_count'];
        });
        
        return $users;
    }

    private function getUserActivities($userId, $dateRange)
    {
        // Get user activities from cache first, then database
        $cacheKey = "user_activity_{$userId}";
        $cachedActivities = Cache::get($cacheKey, []);
        
        // Filter by date range
        $filteredActivities = array_filter($cachedActivities, function($activity) use ($dateRange) {
            $activityTime = Carbon::parse($activity['timestamp']);
            return $activityTime->between($dateRange['start'], $dateRange['end']);
        });
        
        return array_values($filteredActivities);
    }

    private function getUserSessionData($userId, $dateRange)
    {
        return [
            'total_sessions' => rand(15, 50),
            'average_duration' => rand(20, 60) . ' minutes',
            'peak_hours' => ['9:00-11:00', '14:00-16:00'],
            'devices_used' => ['Desktop (Chrome)', 'Mobile (Safari)', 'Tablet (Firefox)'],
            'locations' => ['Office Network', 'Home Network', 'Mobile Network']
        ];
    }

    private function getUserPerformanceMetrics($userId, $dateRange)
    {
        return [
            'productivity_score' => rand(70, 95),
            'tasks_completed' => rand(10, 50),
            'projects_involved' => rand(1, 8),
            'collaboration_score' => rand(60, 90),
            'feature_usage' => [
                'project_management' => rand(30, 90),
                'task_tracking' => rand(40, 95),
                'reporting' => rand(10, 70),
                'team_collaboration' => rand(20, 80)
            ]
        ];
    }

    private function getUserSecurityEvents($userId, $dateRange)
    {
        $events = [];
        $eventTypes = ['failed_login', 'password_change', 'permission_escalation', 'suspicious_activity'];
        
        for ($i = 0; $i < rand(0, 5); $i++) {
            $events[] = [
                'type' => $eventTypes[array_rand($eventTypes)],
                'severity' => ['low', 'medium', 'high'][rand(0, 2)],
                'description' => 'Security event description',
                'timestamp' => Carbon::now()->subHours(rand(1, 168))->format('Y-m-d H:i:s'),
                'resolved' => rand(0, 1) == 1
            ];
        }
        
        return $events;
    }

    private function getOverviewAnalytics($dateRange)
    {
        return [
            'user_engagement' => [
                'daily_active_users' => rand(80, 120),
                'weekly_active_users' => rand(200, 300),
                'monthly_active_users' => rand(500, 800),
                'user_retention_rate' => rand(75, 95) . '%'
            ],
            'feature_usage' => [
                'most_used_features' => [
                    'project_management' => rand(70, 90),
                    'task_tracking' => rand(60, 85),
                    'reporting' => rand(30, 60),
                    'user_management' => rand(20, 40)
                ],
                'feature_adoption_rate' => rand(60, 80) . '%'
            ],
            'performance_metrics' => [
                'average_page_load_time' => rand(800, 1500) . 'ms',
                'error_rate' => rand(1, 5) . '%',
                'user_satisfaction_score' => rand(80, 95) . '%'
            ]
        ];
    }

    private function getBehavioralAnalytics($dateRange)
    {
        return [
            'usage_patterns' => [
                'peak_hours' => ['9:00-11:00', '14:00-16:00', '19:00-21:00'],
                'common_workflows' => [
                    'login -> project_view -> task_update',
                    'login -> dashboard -> reports',
                    'project_create -> team_invite -> task_assign'
                ],
                'session_duration_distribution' => [
                    '0-15 min' => 30,
                    '15-30 min' => 45,
                    '30-60 min' => 20,
                    '60+ min' => 5
                ]
            ],
            'user_journey' => [
                'entry_points' => [
                    'dashboard' => 40,
                    'projects' => 35,
                    'tasks' => 15,
                    'reports' => 10
                ],
                'exit_points' => [
                    'dashboard' => 25,
                    'logout' => 50,
                    'timeout' => 15,
                    'navigation_away' => 10
                ]
            ]
        ];
    }

    private function getSecurityAnalytics($dateRange)
    {
        return [
            'authentication' => [
                'failed_login_attempts' => rand(20, 100),
                'successful_logins' => rand(500, 1000),
                'password_resets' => rand(5, 25),
                'mfa_usage_rate' => rand(60, 90) . '%'
            ],
            'access_patterns' => [
                'unusual_access_times' => rand(0, 10),
                'suspicious_ip_addresses' => rand(0, 5),
                'privilege_escalations' => rand(0, 3),
                'concurrent_sessions' => rand(1, 8)
            ],
            'threat_indicators' => [
                'brute_force_attempts' => rand(0, 15),
                'potential_data_exfiltration' => rand(0, 2),
                'unauthorized_access_attempts' => rand(0, 8),
                'suspicious_activity_patterns' => rand(0, 5)
            ]
        ];
    }

    private function getPerformanceAnalytics($dateRange)
    {
        return [
            'system_performance' => [
                'average_response_time' => rand(200, 800) . 'ms',
                'database_query_time' => rand(50, 200) . 'ms',
                'cache_hit_ratio' => rand(85, 98) . '%',
                'error_rate' => rand(0.1, 2.5) . '%'
            ],
            'user_experience' => [
                'page_load_times' => [
                    'dashboard' => rand(500, 1200) . 'ms',
                    'projects' => rand(600, 1400) . 'ms',
                    'reports' => rand(800, 2000) . 'ms'
                ],
                'user_interaction_delays' => rand(100, 300) . 'ms',
                'bounce_rate' => rand(5, 15) . '%'
            ]
        ];
    }

    private function generateSecurityAlerts($severity, $dateRange)
    {
        $alerts = [];
        $alertTypes = [
            'Failed login attempts',
            'Unusual access pattern',
            'Privilege escalation attempt',
            'Suspicious file access',
            'Multiple concurrent sessions',
            'Access from new location'
        ];
        
        $alertCount = $severity === 'high' ? rand(1, 5) : rand(5, 20);
        
        for ($i = 0; $i < $alertCount; $i++) {
            $alertSeverity = $severity === 'all' ? ['low', 'medium', 'high'][rand(0, 2)] : $severity;
            
            $alerts[] = [
                'id' => $i + 1,
                'type' => $alertTypes[array_rand($alertTypes)],
                'severity' => $alertSeverity,
                'description' => 'Security alert description with details about the potential threat',
                'user_id' => rand(1, 50),
                'user_name' => 'User ' . rand(1, 50),
                'timestamp' => Carbon::now()->subHours(rand(1, 168))->format('Y-m-d H:i:s'),
                'status' => ['open', 'investigating', 'resolved'][rand(0, 2)],
                'risk_score' => rand(1, 100)
            ];
        }
        
        return $alerts;
    }

    private function getResourceAuditTrail($resource, $resourceId, $dateRange)
    {
        // Simulate audit trail data
        $trail = [];
        $actions = ['create', 'read', 'update', 'delete', 'share', 'export'];
        
        for ($i = 0; $i < rand(10, 30); $i++) {
            $trail[] = [
                'id' => $i + 1,
                'action' => $actions[array_rand($actions)],
                'user_name' => 'User ' . rand(1, 20),
                'timestamp' => Carbon::now()->subHours(rand(1, 720))->format('Y-m-d H:i:s'),
                'changes' => json_encode(['field' => 'value']),
                'ip_address' => '192.168.1.' . rand(1, 254)
            ];
        }
        
        return $trail;
    }

    private function getFilteredActivities($dateRange, $filters)
    {
        // This would query the database with filters
        return $this->getRecentActivities(100);
    }

    private function getDateRange($range)
    {
        $end = Carbon::now();
        
        switch ($range) {
            case '24_hours':
                $start = $end->copy()->subDay();
                break;
            case '7_days':
                $start = $end->copy()->subDays(7);
                break;
            case '30_days':
                $start = $end->copy()->subDays(30);
                break;
            case '90_days':
                $start = $end->copy()->subDays(90);
                break;
            default:
                $start = $end->copy()->subDays(30);
        }

        return ['start' => $start, 'end' => $end];
    }

    private function exportToCsv($activities)
    {
        $filename = 'activity_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, ['ID', 'User', 'Action', 'Resource', 'Timestamp', 'IP Address']);
            
            // Write data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity['id'],
                    $activity['user_name'],
                    $activity['action'],
                    $activity['resource'],
                    $activity['timestamp'],
                    $activity['ip_address']
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($activities)
    {
        // Placeholder for Excel export
        return response()->json(['message' => 'Excel export functionality coming soon']);
    }

    private function exportToJson($activities)
    {
        $filename = 'activity_export_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($activities)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}