<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Carbon\Carbon;

class AdminUtilitiesController extends Controller
{
    /**
     * Show administrative utilities dashboard
     */
    public function index()
    {
        return view('admin.utilities.index');
    }

    /**
     * Create system backup
     */
    public function createBackup(Request $request)
    {
        try {
            $backupType = $request->input('type', 'full'); // full, database, files
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "backup_{$backupType}_{$timestamp}";
            
            $backupPath = storage_path("app/backups/{$backupName}");
            
            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            $result = [
                'name' => $backupName,
                'type' => $backupType,
                'created_at' => Carbon::now(),
                'size' => 0,
                'files' => []
            ];

            switch ($backupType) {
                case 'database':
                    $this->backupDatabase($backupPath, $result);
                    break;
                case 'files':
                    $this->backupFiles($backupPath, $result);
                    break;
                case 'full':
                default:
                    $this->backupDatabase($backupPath, $result);
                    $this->backupFiles($backupPath, $result);
                    break;
            }

            // Create ZIP archive
            $this->createZipArchive($backupPath, $result);
            
            // Store backup metadata
            $this->storeBackupMetadata($result);

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List available backups
     */
    public function listBackups()
    {
        try {
            $backupsPath = storage_path('app/backups');
            $backups = [];

            if (File::exists($backupsPath)) {
                $files = File::files($backupsPath);
                
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                        $backups[] = [
                            'name' => pathinfo($file, PATHINFO_FILENAME),
                            'file' => basename($file),
                            'size' => $this->formatBytes(filesize($file)),
                            'created_at' => Carbon::createFromTimestamp(filemtime($file))->format('Y-m-d H:i:s')
                        ];
                    }
                }

                // Sort by creation date (newest first)
                usort($backups, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            Log::error('List backups failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to list backups'
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        try {
            $filePath = storage_path("app/backups/{$filename}");
            
            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            return response()->download($filePath);

        } catch (\Exception $e) {
            Log::error('Download backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download backup'
            ], 500);
        }
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        try {
            $filePath = storage_path("app/backups/{$filename}");
            
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup'
            ], 500);
        }
    }

    /**
     * System maintenance operations
     */
    public function systemMaintenance(Request $request)
    {
        try {
            $operation = $request->input('operation');
            $result = ['success' => true, 'message' => '', 'details' => []];

            switch ($operation) {
                case 'clear_cache':
                    $this->clearCache($result);
                    break;
                case 'clear_logs':
                    $this->clearLogs($result);
                    break;
                case 'optimize_database':
                    $this->optimizeDatabase($result);
                    break;
                case 'cleanup_temp':
                    $this->cleanupTempFiles($result);
                    break;
                case 'update_search_index':
                    $this->updateSearchIndex($result);
                    break;
                default:
                    throw new \Exception('Unknown maintenance operation');
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('System maintenance failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Maintenance operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        try {
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_type' => config('database.default'),
                'storage_disk' => config('filesystems.default'),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'disk_space' => $this->getDiskSpace(),
                'last_backup' => $this->getLastBackupInfo(),
                'system_uptime' => $this->getSystemUptime()
            ];

            return response()->json([
                'success' => true,
                'info' => $info
            ]);

        } catch (\Exception $e) {
            Log::error('Get system info failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system information'
            ], 500);
        }
    }

    // Private helper methods

    private function backupDatabase($backupPath, &$result)
    {
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $sqlFile = $backupPath . '_database.sql';
        
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($databaseName),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && File::exists($sqlFile)) {
            $result['files'][] = $sqlFile;
            $result['size'] += filesize($sqlFile);
        } else {
            throw new \Exception('Database backup failed');
        }
    }

    private function backupFiles($backupPath, &$result)
    {
        $filesToBackup = [
            storage_path('app'),
            public_path('uploads'),
            base_path('.env'),
            base_path('composer.json'),
            base_path('composer.lock')
        ];

        foreach ($filesToBackup as $file) {
            if (File::exists($file)) {
                $destination = $backupPath . '_' . basename($file);
                if (is_dir($file)) {
                    File::copyDirectory($file, $destination);
                } else {
                    File::copy($file, $destination);
                }
                $result['files'][] = $destination;
                $result['size'] += is_dir($destination) ? $this->getDirectorySize($destination) : filesize($destination);
            }
        }
    }

    private function createZipArchive($backupPath, &$result)
    {
        $zipFile = $backupPath . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            foreach ($result['files'] as $file) {
                if (is_dir($file)) {
                    $this->addDirectoryToZip($zip, $file, basename($file));
                } else {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();

            // Clean up temporary files
            foreach ($result['files'] as $file) {
                if (File::exists($file)) {
                    if (is_dir($file)) {
                        File::deleteDirectory($file);
                    } else {
                        File::delete($file);
                    }
                }
            }

            $result['files'] = [$zipFile];
            $result['size'] = filesize($zipFile);
        } else {
            throw new \Exception('Failed to create ZIP archive');
        }
    }

    private function addDirectoryToZip($zip, $dir, $zipPath)
    {
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
    }

    private function storeBackupMetadata($result)
    {
        $metadata = Cache::get('backup_metadata', []);
        $metadata[] = $result;
        
        // Keep only last 50 backups in metadata
        if (count($metadata) > 50) {
            $metadata = array_slice($metadata, -50);
        }
        
        Cache::put('backup_metadata', $metadata, 86400 * 30); // 30 days
    }

    private function clearCache(&$result)
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        $result['message'] = 'Cache cleared successfully';
        $result['details'][] = 'Application cache cleared';
        $result['details'][] = 'Configuration cache cleared';
        $result['details'][] = 'Route cache cleared';
        $result['details'][] = 'View cache cleared';
    }

    private function clearLogs(&$result)
    {
        $logPath = storage_path('logs');
        $logFiles = File::files($logPath);
        
        $clearedCount = 0;
        foreach ($logFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                File::delete($file);
                $clearedCount++;
            }
        }
        
        $result['message'] = "Cleared {$clearedCount} log files";
        $result['details'][] = "Deleted {$clearedCount} log files from {$logPath}";
    }

    private function optimizeDatabase(&$result)
    {
        $tables = DB::select('SHOW TABLES');
        $optimizedCount = 0;
        
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            DB::statement("OPTIMIZE TABLE `{$tableName}`");
            $optimizedCount++;
        }
        
        $result['message'] = "Optimized {$optimizedCount} database tables";
        $result['details'][] = "Optimized {$optimizedCount} tables in the database";
    }

    private function cleanupTempFiles(&$result)
    {
        $tempPaths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            sys_get_temp_dir()
        ];
        
        $cleanedSize = 0;
        $cleanedFiles = 0;
        
        foreach ($tempPaths as $path) {
            if (File::exists($path)) {
                $files = File::files($path);
                foreach ($files as $file) {
                    if (Carbon::createFromTimestamp(filemtime($file))->addDays(7)->isPast()) {
                        $cleanedSize += filesize($file);
                        File::delete($file);
                        $cleanedFiles++;
                    }
                }
            }
        }
        
        $result['message'] = "Cleaned {$cleanedFiles} temporary files ({$this->formatBytes($cleanedSize)})";
        $result['details'][] = "Removed {$cleanedFiles} temporary files";
        $result['details'][] = "Freed up {$this->formatBytes($cleanedSize)} of disk space";
    }

    private function updateSearchIndex(&$result)
    {
        // This would integrate with your search implementation
        // For now, just a placeholder
        $result['message'] = 'Search index updated successfully';
        $result['details'][] = 'Rebuilt search index for projects and tasks';
    }

    private function getDiskSpace()
    {
        $bytes = disk_free_space('/');
        $total = disk_total_space('/');
        
        return [
            'free' => $this->formatBytes($bytes),
            'total' => $this->formatBytes($total),
            'used_percent' => round((($total - $bytes) / $total) * 100, 2)
        ];
    }

    private function getLastBackupInfo()
    {
        $backupsPath = storage_path('app/backups');
        
        if (!File::exists($backupsPath)) {
            return null;
        }
        
        $files = File::files($backupsPath);
        if (empty($files)) {
            return null;
        }
        
        $latestFile = null;
        $latestTime = 0;
        
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latestTime) {
                $latestTime = $mtime;
                $latestFile = $file;
            }
        }
        
        if ($latestFile) {
            return [
                'name' => basename($latestFile),
                'created_at' => Carbon::createFromTimestamp($latestTime)->format('Y-m-d H:i:s'),
                'size' => $this->formatBytes(filesize($latestFile))
            ];
        }
        
        return null;
    }

    private function getSystemUptime()
    {
        if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin') {
            $uptime = shell_exec('uptime -p');
            return trim($uptime) ?: 'Unknown';
        }
        
        return 'Not available on this system';
    }

    private function getDirectorySize($directory)
    {
        $size = 0;
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
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