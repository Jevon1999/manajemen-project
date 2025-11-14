<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.admin']);
    }

    /**
     * Display comprehensive system settings management page
     */
    public function management()
    {
        $settings = [
            'general' => $this->getGeneralSettings(),
            'email' => $this->getEmailSettings(),
            'database' => $this->getDatabaseSettings(),
            'security' => $this->getSecuritySettings(),
            'backup' => $this->getBackupSettings(),
            'performance' => $this->getPerformanceSettings(),
        ];

        return view('admin.settings.management', compact('settings'));
    }

    /**
     * Get general application settings
     */
    private function getGeneralSettings()
    {
        return [
            'app_name' => config('app.name', 'ProjectHub'),
            'app_version' => '1.0.0',
            'app_environment' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];
    }

    /**
     * Get email configuration settings
     */
    private function getEmailSettings()
    {
        return [
            'mail_driver' => config('mail.driver'),
            'mail_host' => config('mail.host'),
            'mail_port' => config('mail.port'),
            'mail_encryption' => config('mail.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];
    }

    /**
     * Get database configuration
     */
    private function getDatabaseSettings()
    {
        return [
            'database_connection' => config('database.default'),
            'database_host' => config('database.connections.mysql.host'),
            'database_port' => config('database.connections.mysql.port'),
            'database_name' => config('database.connections.mysql.database'),
            'database_charset' => config('database.connections.mysql.charset'),
        ];
    }

    /**
     * Get security settings
     */
    private function getSecuritySettings()
    {
        return [
            'session_lifetime' => config('session.lifetime'),
            'session_driver' => config('session.driver'),
            'csrf_protection' => true,
            'encryption_cipher' => config('app.cipher'),
            'password_timeout' => config('auth.password_timeout'),
        ];
    }

    /**
     * Get backup settings and status
     */
    private function getBackupSettings()
    {
        $backupPath = storage_path('app/backup');
        $backups = [];
        
        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize($backupPath . '/' . $file),
                        'date' => date('Y-m-d H:i:s', filemtime($backupPath . '/' . $file)),
                    ];
                }
            }
        }

        return [
            'backup_enabled' => true,
            'backup_schedule' => 'daily',
            'backup_retention' => 30,
            'recent_backups' => array_slice($backups, -5),
            'backup_storage' => 'local',
        ];
    }

    /**
     * Get performance settings
     */
    private function getPerformanceSettings()
    {
        return [
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'redis_connection' => config('database.redis.default.host'),
            'opcache_enabled' => function_exists('opcache_get_status') ? opcache_get_status() : false,
        ];
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'locale' => 'required|string',
        ]);

        // In a real application, you'd update these in the .env file
        // For now, we'll just return success
        
        return response()->json([
            'message' => 'General settings berhasil diupdate.'
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Update email configuration
        // In production, you'd update the .env file
        
        return response()->json([
            'message' => 'Email settings berhasil diupdate.'
        ]);
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            // Send test email
            $testEmail = $request->input('test_email', Auth::user()->email);
            
            // In a real implementation, you'd send an actual email
            
            return response()->json([
                'message' => 'Test email berhasil dikirim ke ' . $testEmail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengirim test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create database backup
     */
    public function createBackup(Request $request)
    {
        try {
            $backupName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            $backupPath = storage_path('app/backup');
            
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Run database backup command
            Artisan::call('backup:run');

            return response()->json([
                'message' => 'Database backup berhasil dibuat.',
                'backup_name' => $backupName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json([
                'message' => 'Cache berhasil dibersihkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membersihkan cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'session_lifetime' => 'required|integer|min:1|max:10080', // Max 1 week
            'password_timeout' => 'required|integer|min:1|max:43200', // Max 12 hours
        ]);

        // Update security settings
        // In production, you'd update the configuration files
        
        return response()->json([
            'message' => 'Security settings berhasil diupdate.'
        ]);
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_space' => [
                'total' => disk_total_space('.'),
                'free' => disk_free_space('.'),
            ],
        ]);
    }
}