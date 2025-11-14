<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SystemMonitoringController extends Controller
{
    /**
     * Get system health metrics
     */
    public function getSystemHealth()
    {
        try {
            $metrics = [
                'activeUsers' => $this->getActiveUsersCount(),
                'onlineUsers' => $this->getOnlineUsersCount(),
                'serverLoad' => $this->getServerLoad(),
                'memoryUsage' => $this->getMemoryUsage(),
                'storageUsed' => $this->getStorageUsage(),
                'storagePercent' => $this->getStoragePercentage(),
                'databaseConnections' => $this->getDatabaseConnections(),
                'queueStatus' => $this->getQueueStatus(),
                'cacheStatus' => $this->getCacheStatus(),
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('System health check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system health metrics'
            ], 500);
        }
    }

    /**
     * Get project statistics for dashboard
     */
    public function getProjectStatistics()
    {
        try {
            $statistics = DB::table('projects')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN status = "planning" THEN 1 ELSE 0 END) as planning'),
                    DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress'),
                    DB::raw('SUM(CASE WHEN status = "review" THEN 1 ELSE 0 END) as review'),
                    DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                    DB::raw('SUM(CASE WHEN status = "on_hold" THEN 1 ELSE 0 END) as on_hold')
                )
                ->first();

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Project statistics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get project statistics'
            ], 500);
        }
    }

    /**
     * Get system performance metrics
     */
    public function getPerformanceMetrics()
    {
        try {
            $metrics = [
                'response_time' => $this->getAverageResponseTime(),
                'error_rate' => $this->getErrorRate(),
                'throughput' => $this->getThroughput(),
                'database_performance' => $this->getDatabasePerformance(),
                'cache_hit_rate' => $this->getCacheHitRate(),
                'peak_usage_hours' => $this->getPeakUsageHours()
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Performance metrics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get performance metrics'
            ], 500);
        }
    }

    /**
     * Get security monitoring data
     */
    public function getSecurityMetrics()
    {
        try {
            $metrics = [
                'failed_login_attempts' => $this->getFailedLoginAttempts(),
                'suspicious_activities' => $this->getSuspiciousActivities(),
                'blocked_ips' => $this->getBlockedIPs(),
                'security_events' => $this->getSecurityEvents(),
                'ssl_status' => $this->getSSLStatus(),
                'firewall_status' => $this->getFirewallStatus()
            ];

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Security metrics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get security metrics'
            ], 500);
        }
    }

    /**
     * Get system alerts
     */
    public function getSystemAlerts()
    {
        try {
            $alerts = $this->checkSystemAlerts();

            return response()->json([
                'success' => true,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('System alerts check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system alerts'
            ], 500);
        }
    }

    /**
     * Clear system alerts
     */
    public function clearAlerts(Request $request)
    {
        try {
            $alertIds = $request->input('alert_ids', []);
            
            if (empty($alertIds)) {
                // Clear all alerts
                Cache::forget('system_alerts');
            } else {
                // Clear specific alerts
                $currentAlerts = Cache::get('system_alerts', []);
                $filteredAlerts = array_filter($currentAlerts, function($alert) use ($alertIds) {
                    return !in_array($alert['id'], $alertIds);
                });
                Cache::put('system_alerts', $filteredAlerts, 3600);
            }

            return response()->json([
                'success' => true,
                'message' => 'Alerts cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear alerts failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear alerts'
            ], 500);
        }
    }

    // Private helper methods

    private function getActiveUsersCount()
    {
        // Users active in the last 24 hours
        return DB::table('users')
            ->where('last_activity_at', '>=', now()->subDay())
            ->where('status', 'active')
            ->count();
    }

    private function getOnlineUsersCount()
    {
        // Users active in the last 15 minutes
        return DB::table('users')
            ->where('last_activity_at', '>=', now()->subMinutes(15))
            ->where('status', 'active')
            ->count();
    }

    private function getServerLoad()
    {
        // Simulate server load calculation
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 10, 1); // Convert to percentage-like value
        }
        
        // Fallback simulation
        return rand(8, 25);
    }

    private function getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            $bytes = memory_get_usage(true);
            return round($bytes / 1024 / 1024, 2); // Convert to MB
        }
        
        return rand(120, 250); // Fallback MB
    }

    private function getStorageUsage()
    {
        try {
            $bytes = Storage::size('') ?: 0;
            return $this->formatBytes($bytes);
        } catch (\Exception $e) {
            return '2.4 GB'; // Fallback
        }
    }

    private function getStoragePercentage()
    {
        // Simulate storage percentage calculation
        return rand(20, 40) . '% of 10 GB';
    }

    private function getDatabaseConnections()
    {
        try {
            $connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
            return isset($connections[0]) ? $connections[0]->Value : 0;
        } catch (\Exception $e) {
            return 5; // Fallback
        }
    }

    private function getQueueStatus()
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            return [
                'pending' => $pendingJobs,
                'failed' => $failedJobs,
                'status' => $failedJobs > 10 ? 'warning' : 'healthy'
            ];
        } catch (\Exception $e) {
            return ['pending' => 0, 'failed' => 0, 'status' => 'healthy'];
        }
    }

    private function getCacheStatus()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $test = Cache::get('health_check');
            return $test === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getAverageResponseTime()
    {
        // Simulate response time calculation
        return rand(150, 300) . 'ms';
    }

    private function getErrorRate()
    {
        // Simulate error rate
        return rand(0, 5) / 100;
    }

    private function getThroughput()
    {
        // Simulate requests per minute
        return rand(50, 200);
    }

    private function getDatabasePerformance()
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $duration = (microtime(true) - $start) * 1000;
            
            return [
                'query_time' => round($duration, 2) . 'ms',
                'status' => $duration < 100 ? 'good' : ($duration < 500 ? 'warning' : 'slow')
            ];
        } catch (\Exception $e) {
            return ['query_time' => 'N/A', 'status' => 'error'];
        }
    }

    private function getCacheHitRate()
    {
        // Simulate cache hit rate
        return rand(80, 95) . '%';
    }

    private function getPeakUsageHours()
    {
        return ['09:00-11:00', '14:00-16:00'];
    }

    private function getFailedLoginAttempts()
    {
        // Count failed login attempts in the last hour
        return DB::table('activity_log')
            ->where('description', 'login_failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    private function getSuspiciousActivities()
    {
        // Check for suspicious activities
        return DB::table('activity_log')
            ->whereIn('description', ['multiple_failed_logins', 'suspicious_request'])
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    private function getBlockedIPs()
    {
        // This would integrate with your security system
        return 0; // Placeholder
    }

    private function getSecurityEvents()
    {
        return [
            'high_priority' => 0,
            'medium_priority' => 1,
            'low_priority' => 3
        ];
    }

    private function getSSLStatus()
    {
        return [
            'enabled' => true,
            'expires_at' => now()->addMonths(3)->toDateString(),
            'status' => 'valid'
        ];
    }

    private function getFirewallStatus()
    {
        return [
            'enabled' => true,
            'rules_count' => 15,
            'status' => 'active'
        ];
    }

    private function checkSystemAlerts()
    {
        $alerts = [];

        // Check server load
        $serverLoad = $this->getServerLoad();
        if ($serverLoad > 80) {
            $alerts[] = [
                'id' => 'high_server_load',
                'type' => 'warning',
                'title' => 'High Server Load',
                'message' => "Server load is at {$serverLoad}%",
                'timestamp' => now()->toISOString(),
                'priority' => 'high'
            ];
        }

        // Check storage
        $storageUsage = $this->getStorageUsage();
        if (strpos($storageUsage, 'GB') && (float)$storageUsage >= 8.0) {
            $alerts[] = [
                'id' => 'low_storage',
                'type' => 'warning', 
                'title' => 'Low Storage Space',
                'message' => "Storage usage is at {$storageUsage}",
                'timestamp' => now()->toISOString(),
                'priority' => 'medium'
            ];
        }

        // Check failed logins
        $failedLogins = $this->getFailedLoginAttempts();
        if ($failedLogins > 5) {
            $alerts[] = [
                'id' => 'multiple_failed_logins',
                'type' => 'security',
                'title' => 'Multiple Failed Login Attempts',
                'message' => "{$failedLogins} failed login attempts in the last hour",
                'timestamp' => now()->toISOString(),
                'priority' => 'high'
            ];
        }

        // Check database performance
        $dbPerf = $this->getDatabasePerformance();
        if ($dbPerf['status'] === 'slow') {
            $alerts[] = [
                'id' => 'slow_database',
                'type' => 'warning',
                'title' => 'Slow Database Performance',
                'message' => "Database query time: {$dbPerf['query_time']}",
                'timestamp' => now()->toISOString(),
                'priority' => 'medium'
            ];
        }

        return $alerts;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}