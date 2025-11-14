@extends('layout.app')

@section('title', 'Administrative Utilities')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Administrative Utilities</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Administrative Utilities</li>
            </ol>
        </nav>
    </div>

    <!-- System Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSystemInfo()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="system-info-loading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div id="system-info-content" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><td><strong>PHP Version:</strong></td><td id="php-version">-</td></tr>
                                    <tr><td><strong>Laravel Version:</strong></td><td id="laravel-version">-</td></tr>
                                    <tr><td><strong>Server Software:</strong></td><td id="server-software">-</td></tr>
                                    <tr><td><strong>Database:</strong></td><td id="database-type">-</td></tr>
                                    <tr><td><strong>Cache Driver:</strong></td><td id="cache-driver">-</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><td><strong>Memory Limit:</strong></td><td id="memory-limit">-</td></tr>
                                    <tr><td><strong>Max Execution Time:</strong></td><td id="max-execution-time">-</td></tr>
                                    <tr><td><strong>Upload Max Size:</strong></td><td id="upload-max-filesize">-</td></tr>
                                    <tr><td><strong>Disk Space:</strong></td><td id="disk-space">-</td></tr>
                                    <tr><td><strong>Last Backup:</strong></td><td id="last-backup">-</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Backup Management -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Backup Management</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="backup-type" class="form-label">Backup Type</label>
                        <select class="form-select" id="backup-type">
                            <option value="full">Full Backup (Database + Files)</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-primary" onclick="createBackup()">
                            <i class="fas fa-archive"></i> Create Backup
                        </button>
                        <button class="btn btn-outline-secondary" onclick="refreshBackupList()">
                            <i class="fas fa-list"></i> View Backup History
                        </button>
                    </div>

                    <div id="backup-progress" style="display: none;">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Creating backup...</small>
                    </div>

                    <div id="backup-list-container" style="display: none;">
                        <h6 class="font-weight-bold mb-2">Recent Backups</h6>
                        <div id="backup-list" class="list-group">
                            <!-- Backup list will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">System Maintenance</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100" onclick="performMaintenance('clear_cache')">
                                <i class="fas fa-broom"></i><br>Clear Cache
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100" onclick="performMaintenance('clear_logs')">
                                <i class="fas fa-file-alt"></i><br>Clear Logs
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100" onclick="performMaintenance('optimize_database')">
                                <i class="fas fa-database"></i><br>Optimize DB
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100" onclick="performMaintenance('cleanup_temp')">
                                <i class="fas fa-trash"></i><br>Cleanup Temp
                            </button>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-info w-100" onclick="performMaintenance('update_search_index')">
                                <i class="fas fa-search"></i> Update Search Index
                            </button>
                        </div>
                    </div>

                    <div id="maintenance-result" class="mt-3" style="display: none;">
                        <div class="alert" id="maintenance-alert">
                            <h6 id="maintenance-message"></h6>
                            <ul id="maintenance-details" class="mb-0"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Tools -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Advanced Tools</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="text-info">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="font-weight-bold">Performance Monitor</h6>
                                        <p class="text-muted small">Monitor system performance and resource usage</p>
                                        <button class="btn btn-sm btn-info" onclick="openPerformanceMonitor()">
                                            Open Monitor
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="text-success">
                                        <i class="fas fa-cogs fa-2x"></i>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="font-weight-bold">Configuration Manager</h6>
                                        <p class="text-muted small">Manage application configuration settings</p>
                                        <button class="btn btn-sm btn-success" onclick="openConfigManager()">
                                            Manage Config
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-danger h-100">
                                <div class="card-body">
                                    <div class="text-danger">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <div class="mt-2">
                                        <h6 class="font-weight-bold">Error Logs</h6>
                                        <p class="text-muted small">View and manage application error logs</p>
                                        <button class="btn btn-sm btn-danger" onclick="openErrorLogs()">
                                            View Logs
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup History Modal -->
<div class="modal fade" id="backupHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Backup History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="backup-history-loading" class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="backup-history-content" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="backup-history-table">
                                <!-- History will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let maintenanceInProgress = false;

// Load system information on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshSystemInfo();
});

function refreshSystemInfo() {
    fetch('/admin/utilities/system-info')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const info = data.info;
                document.getElementById('php-version').textContent = info.php_version;
                document.getElementById('laravel-version').textContent = info.laravel_version;
                document.getElementById('server-software').textContent = info.server_software;
                document.getElementById('database-type').textContent = info.database_type;
                document.getElementById('cache-driver').textContent = info.cache_driver;
                document.getElementById('memory-limit').textContent = info.memory_limit;
                document.getElementById('max-execution-time').textContent = info.max_execution_time + 's';
                document.getElementById('upload-max-filesize').textContent = info.upload_max_filesize;
                
                if (info.disk_space) {
                    document.getElementById('disk-space').textContent = 
                        `${info.disk_space.free} free of ${info.disk_space.total} (${info.disk_space.used_percent}% used)`;
                }
                
                if (info.last_backup) {
                    document.getElementById('last-backup').textContent = 
                        `${info.last_backup.name} (${info.last_backup.created_at})`;
                } else {
                    document.getElementById('last-backup').textContent = 'No backups found';
                }
                
                document.getElementById('system-info-loading').style.display = 'none';
                document.getElementById('system-info-content').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading system info:', error);
            showAlert('Error loading system information', 'danger');
        });
}

function createBackup() {
    const backupType = document.getElementById('backup-type').value;
    const progressContainer = document.getElementById('backup-progress');
    const progressBar = progressContainer.querySelector('.progress-bar');
    
    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    
    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 500);

    fetch('/admin/utilities/backup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ type: backupType })
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        setTimeout(() => {
            progressContainer.style.display = 'none';
            
            if (data.success) {
                showAlert('Backup created successfully!', 'success');
                refreshSystemInfo(); // Refresh to update last backup info
            } else {
                showAlert('Failed to create backup: ' + data.message, 'danger');
            }
        }, 1000);
    })
    .catch(error => {
        clearInterval(progressInterval);
        progressContainer.style.display = 'none';
        console.error('Error creating backup:', error);
        showAlert('Error creating backup', 'danger');
    });
}

function refreshBackupList() {
    const modal = new bootstrap.Modal(document.getElementById('backupHistoryModal'));
    modal.show();
    
    document.getElementById('backup-history-loading').style.display = 'block';
    document.getElementById('backup-history-content').style.display = 'none';
    
    fetch('/admin/utilities/backups')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('backup-history-table');
                tbody.innerHTML = '';
                
                if (data.backups.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No backups found</td></tr>';
                } else {
                    data.backups.forEach(backup => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${backup.name}</td>
                            <td>${backup.size}</td>
                            <td>${backup.created_at}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="downloadBackup('${backup.file}')">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-danger ms-1" onclick="deleteBackup('${backup.file}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                }
                
                document.getElementById('backup-history-loading').style.display = 'none';
                document.getElementById('backup-history-content').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading backup history:', error);
            showAlert('Error loading backup history', 'danger');
        });
}

function downloadBackup(filename) {
    window.location.href = `/admin/utilities/backup/${filename}/download`;
}

function deleteBackup(filename) {
    if (confirm('Are you sure you want to delete this backup?')) {
        fetch(`/admin/utilities/backup/${filename}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Backup deleted successfully', 'success');
                refreshBackupList();
            } else {
                showAlert('Failed to delete backup: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting backup:', error);
            showAlert('Error deleting backup', 'danger');
        });
    }
}

function performMaintenance(operation) {
    if (maintenanceInProgress) {
        showAlert('Another maintenance operation is in progress', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to perform this maintenance operation: ${operation.replace('_', ' ')}?`)) {
        return;
    }
    
    maintenanceInProgress = true;
    const resultContainer = document.getElementById('maintenance-result');
    resultContainer.style.display = 'none';
    
    // Show loading state
    showAlert('Performing maintenance operation...', 'info');
    
    fetch('/admin/utilities/maintenance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ operation: operation })
    })
    .then(response => response.json())
    .then(data => {
        maintenanceInProgress = false;
        
        const alertDiv = document.getElementById('maintenance-alert');
        const messageDiv = document.getElementById('maintenance-message');
        const detailsList = document.getElementById('maintenance-details');
        
        if (data.success) {
            alertDiv.className = 'alert alert-success';
            messageDiv.textContent = data.message;
            
            detailsList.innerHTML = '';
            if (data.details) {
                data.details.forEach(detail => {
                    const li = document.createElement('li');
                    li.textContent = detail;
                    detailsList.appendChild(li);
                });
            }
            
            resultContainer.style.display = 'block';
        } else {
            showAlert('Maintenance operation failed: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        maintenanceInProgress = false;
        console.error('Error performing maintenance:', error);
        showAlert('Error performing maintenance operation', 'danger');
    });
}

function openPerformanceMonitor() {
    // Placeholder for performance monitor
    showAlert('Performance Monitor feature coming soon!', 'info');
}

function openConfigManager() {
    // Placeholder for configuration manager
    showAlert('Configuration Manager feature coming soon!', 'info');
}

function openErrorLogs() {
    // Placeholder for error logs viewer
    showAlert('Error Logs Viewer feature coming soon!', 'info');
}

function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush