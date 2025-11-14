<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Access denied. Admin role required.');
            }
            return $next($request);
        });
    }

    public function management()
    {
        $settings = [
            'general' => [
                'app_name' => config('app.name', 'Project Management'),
                'app_url' => config('app.url', url('/')),
                'timezone' => config('app.timezone', 'UTC'),
                'locale' => config('app.locale', 'en'),
            ],
            'database' => [
                'database_connection' => config('database.default', 'mysql'),
            ],
            'email' => [
                'mail_host' => config('mail.mailers.smtp.host', 'smtp.mailtrap.io'),
                'mail_port' => config('mail.mailers.smtp.port', 2525),
                'mail_from_address' => config('mail.from.address', 'noreply@example.com'),
                'mail_from_name' => config('mail.from.name', config('app.name')),
            ],
            'security' => [
                'session_lifetime' => config('session.lifetime', 120),
                'session_driver' => config('session.driver', 'file'),
                'password_timeout' => config('auth.password_timeout', 10800),
                'encryption_cipher' => config('app.cipher', 'AES-256-CBC'),
                'csrf_protection' => true,
            ],
            'backup' => [
                'backup_schedule' => 'daily',
                'backup_retention' => 30,
                'backup_storage' => 'local',
                'recent_backups' => $this->getRecentBackups(),
            ],
            'performance' => [
                'cache_driver' => config('cache.default', 'file'),
                'queue_driver' => config('queue.default', 'sync'),
                'redis_connection' => $this->checkRedisConnection(),
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status(),
            ],
        ];

        return view('admin.settings.management', compact('settings'));
    }

    // General Settings Management
    public function generalSettings(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'app_name' => 'required|string|max:255',
                'timezone' => 'required|string',
                'locale' => 'required|string|in:en,id',
            ]);

            foreach ($validatedData as $key => $value) {
                config([$key => $value]);
            }

            return response()->json([
                'success' => true,
                'message' => 'General settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating general settings: ' . $e->getMessage()
            ], 500);
        }
    }

    // Email Settings Management
    public function emailSettings(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mail_host' => 'required|string',
                'mail_port' => 'required|integer',
                'mail_username' => 'nullable|string',
                'mail_password' => 'nullable|string',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string',
            ]);

            // Update mail configuration
            foreach ($validatedData as $key => $value) {
                config(['mail.' . str_replace('mail_', '', $key) => $value]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating email settings: ' . $e->getMessage()
            ], 500);
        }
    }

    // Security Settings Management
    public function securitySettings(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'session_lifetime' => 'required|integer|min:1|max:10080',
                'password_timeout' => 'required|integer|min:1|max:43200',
            ]);

            foreach ($validatedData as $key => $value) {
                config(['session.' . str_replace('_timeout', '', $key) => $value]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Security settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating security settings: ' . $e->getMessage()
            ], 500);
        }
    }

    // Email Testing
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email'
            ]);

            $email = $request->test_email;
            
            // Send test email
            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from ' . config('app.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending test email: ' . $e->getMessage()
            ], 500);
        }
    }

    // Backup Management
    public function createBackup()
    {
        try {
            // Run database backup command
            Artisan::call('db:backup');
            
            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cache Management
    public function clearCache()
    {
        try {
            // Clear various cache types
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    // System Information
    public function getSystemInfo()
    {
        try {
            $systemInfo = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'disk_space' => [
                    'free' => disk_free_space(storage_path()),
                    'total' => disk_total_space(storage_path())
                ]
            ];

            return response()->json($systemInfo);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to retrieve system information'
            ], 500);
        }
    }

    // Helper Methods
    private function getRecentBackups()
    {
        $backupsPath = storage_path('app/backups');
        
        if (!is_dir($backupsPath)) {
            return [];
        }

        $backups = [];
        $files = glob($backupsPath . '/*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'size' => filesize($file),
                'path' => $file
            ];
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($backups, 0, 5); // Return last 5 backups
    }

    private function checkRedisConnection()
    {
        try {
            if (config('cache.default') === 'redis') {
                Cache::store('redis')->put('test', 'connection', 1);
                return 'Connected';
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}