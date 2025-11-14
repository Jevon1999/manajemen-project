@extends('layout.app')

@section('title', 'Advanced Reporting System')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Advanced Reporting System</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Advanced Reports</li>
            </ol>
        </nav>
    </div>

    <!-- Report Categories -->
    <div class="row mb-4">
        @foreach($reportCategories as $categoryKey => $category)
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $category['name'] }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ $category['description'] }}</p>
                    <div class="list-group list-group-flush">
                        @foreach($category['reports'] as $reportKey => $reportName)
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span>{{ $reportName }}</span>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="openReportModal('{{ $reportKey }}', '{{ $reportName }}')">
                                <i class="fas fa-chart-bar"></i> Generate
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-success w-100 mb-2" onclick="generateQuickReport('weekly_summary')">
                                <i class="fas fa-calendar-week"></i><br>
                                Weekly Summary
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info w-100 mb-2" onclick="generateQuickReport('monthly_analytics')">
                                <i class="fas fa-calendar-alt"></i><br>
                                Monthly Analytics
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning w-100 mb-2" onclick="openTemplateManager()">
                                <i class="fas fa-file-alt"></i><br>
                                Report Templates
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary w-100 mb-2" onclick="openScheduledReports()">
                                <i class="fas fa-clock"></i><br>
                                Scheduled Reports
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Recent Reports</h6>
                    <button class="btn btn-sm btn-outline-info" onclick="refreshRecentReports()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="recent-reports-container">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>No recent reports found. Generate your first report above!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalTitle">Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Report Configuration -->
                <div id="report-config" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="date-range" class="form-label">Date Range</label>
                            <select class="form-select" id="date-range">
                                <option value="7_days">Last 7 Days</option>
                                <option value="30_days" selected>Last 30 Days</option>
                                <option value="90_days">Last 90 Days</option>
                                <option value="1_year">Last Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="export-format" class="form-label">Export Format</label>
                            <select class="form-select" id="export-format">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-primary" onclick="generateReport()">
                                    <i class="fas fa-play"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#advanced-filters">
                            <i class="fas fa-filter"></i> Advanced Filters
                        </button>
                    </div>

                    <div class="collapse mt-3" id="advanced-filters">
                        <div class="card card-body">
                            <div class="row" id="filter-container">
                                <!-- Filters will be populated based on report type -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Generation Progress -->
                <div id="report-progress" style="display: none;">
                    <div class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Generating...</span>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progress-bar"></div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted" id="progress-message">Initializing report generation...</small>
                    </div>
                </div>

                <!-- Report Content -->
                <div id="report-content" style="display: none;">
                    <div class="row mb-3">
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Report Results</h6>
                                <div>
                                    <button class="btn btn-sm btn-success" onclick="exportCurrentReport()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="scheduleReport()">
                                        <i class="fas fa-clock"></i> Schedule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4" id="summary-cards">
                        <!-- Summary cards will be populated here -->
                    </div>

                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Primary Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="primary-chart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Secondary Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="secondary-chart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Detailed Data</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="report-data-table">
                                            <thead id="table-header">
                                                <!-- Table headers will be populated -->
                                            </thead>
                                            <tbody id="table-body">
                                                <!-- Table data will be populated -->
                                            </tbody>
                                        </table>
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

<!-- Template Manager Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="template-list">
                    <!-- Templates will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentReportType = null;
let currentReportData = null;

// Report generation functionality
function openReportModal(reportType, reportName) {
    currentReportType = reportType;
    document.getElementById('reportModalTitle').textContent = `Generate ${reportName}`;
    
    // Reset modal state
    document.getElementById('report-config').style.display = 'block';
    document.getElementById('report-progress').style.display = 'none';
    document.getElementById('report-content').style.display = 'none';
    
    // Setup filters based on report type
    setupReportFilters(reportType);
    
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    modal.show();
}

function setupReportFilters(reportType) {
    const filterContainer = document.getElementById('filter-container');
    filterContainer.innerHTML = '';
    
    // Common filters for all reports
    const commonFilters = `
        <div class="col-md-6 mb-3">
            <label class="form-label">Projects</label>
            <select class="form-select" id="filter-projects" multiple>
                <option value="">All Projects</option>
                <!-- Projects will be populated dynamically -->
            </select>
        </div>
    `;
    
    // Report-specific filters
    let specificFilters = '';
    
    switch(reportType) {
        case 'team_productivity':
        case 'individual_performance':
            specificFilters = `
                <div class="col-md-6 mb-3">
                    <label class="form-label">Team Roles</label>
                    <select class="form-select" id="filter-roles" multiple>
                        <option value="leader">Team Leader</option>
                        <option value="member">Team Member</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            `;
            break;
        case 'task_completion':
            specificFilters = `
                <div class="col-md-6 mb-3">
                    <label class="form-label">Task Priority</label>
                    <select class="form-select" id="filter-priority" multiple>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            `;
            break;
    }
    
    filterContainer.innerHTML = commonFilters + specificFilters;
    
    // Load project options
    loadProjectOptions();
}

function loadProjectOptions() {
    // Fetch projects for filter dropdown
    fetch('/admin/project-admin/projects-list')
        .then(response => response.json())
        .then(data => {
            const projectSelect = document.getElementById('filter-projects');
            if (projectSelect && data.success) {
                data.projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.project_id;
                    option.textContent = project.name;
                    projectSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading projects:', error));
}

function generateReport() {
    if (!currentReportType) {
        showAlert('No report type selected', 'error');
        return;
    }
    
    // Show progress
    document.getElementById('report-config').style.display = 'none';
    document.getElementById('report-progress').style.display = 'block';
    
    // Collect form data
    const dateRange = document.getElementById('date-range').value;
    const filters = collectFilters();
    
    // Simulate progress
    simulateProgress();
    
    // Generate report
    fetch('/admin/reports/advanced/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            report_type: currentReportType,
            date_range: dateRange,
            filters: filters
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentReportData = data;
            displayReportResults(data);
        } else {
            showAlert('Failed to generate report: ' + data.message, 'error');
            resetModalState();
        }
    })
    .catch(error => {
        console.error('Error generating report:', error);
        showAlert('Error generating report', 'error');
        resetModalState();
    });
}

function collectFilters() {
    const filters = {};
    
    // Collect project filters
    const projectSelect = document.getElementById('filter-projects');
    if (projectSelect) {
        const selectedProjects = Array.from(projectSelect.selectedOptions).map(option => option.value);
        if (selectedProjects.length > 0 && selectedProjects[0] !== '') {
            filters.project_ids = selectedProjects;
        }
    }
    
    // Collect role filters
    const roleSelect = document.getElementById('filter-roles');
    if (roleSelect) {
        const selectedRoles = Array.from(roleSelect.selectedOptions).map(option => option.value);
        if (selectedRoles.length > 0) {
            filters.team_roles = selectedRoles;
        }
    }
    
    // Collect priority filters
    const prioritySelect = document.getElementById('filter-priority');
    if (prioritySelect) {
        const selectedPriorities = Array.from(prioritySelect.selectedOptions).map(option => option.value);
        if (selectedPriorities.length > 0) {
            filters.priorities = selectedPriorities;
        }
    }
    
    return filters;
}

function simulateProgress() {
    const progressBar = document.getElementById('progress-bar');
    const progressMessage = document.getElementById('progress-message');
    
    const steps = [
        { percentage: 20, message: 'Collecting data...' },
        { percentage: 40, message: 'Processing analytics...' },
        { percentage: 60, message: 'Generating charts...' },
        { percentage: 80, message: 'Compiling results...' },
        { percentage: 100, message: 'Finalizing report...' }
    ];
    
    let currentStep = 0;
    
    const updateProgress = () => {
        if (currentStep < steps.length) {
            const step = steps[currentStep];
            progressBar.style.width = step.percentage + '%';
            progressMessage.textContent = step.message;
            currentStep++;
            setTimeout(updateProgress, 800);
        }
    };
    
    updateProgress();
}

function displayReportResults(data) {
    // Hide progress, show results
    document.getElementById('report-progress').style.display = 'none';
    document.getElementById('report-content').style.display = 'block';
    
    // Display summary cards
    displaySummaryCards(data.data.summary);
    
    // Display charts
    displayCharts(data.data.charts);
    
    // Display data table
    displayDataTable(data.data);
}

function displaySummaryCards(summary) {
    const container = document.getElementById('summary-cards');
    container.innerHTML = '';
    
    if (!summary) return;
    
    Object.entries(summary).forEach(([key, value]) => {
        if (typeof value === 'number' || typeof value === 'string') {
            const card = document.createElement('div');
            card.className = 'col-md-3 mb-3';
            card.innerHTML = `
                <div class="card border-left-primary h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    ${key.replace(/_/g, ' ')}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ${typeof value === 'number' ? value.toLocaleString() : value}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
        }
    });
}

function displayCharts(charts) {
    // Placeholder for chart rendering
    // Would integrate with Chart.js to create actual charts
    console.log('Charts data:', charts);
}

function displayDataTable(reportData) {
    const tableHeader = document.getElementById('table-header');
    const tableBody = document.getElementById('table-body');
    
    // Clear existing content
    tableHeader.innerHTML = '';
    tableBody.innerHTML = '';
    
    // Determine data source based on report type
    let dataArray = [];
    let headers = [];
    
    if (reportData.projects) {
        dataArray = reportData.projects;
        headers = ['Project Name', 'Status', 'Completion Rate', 'Total Tasks', 'Team Size'];
    } else if (reportData.users) {
        dataArray = reportData.users;
        headers = ['User Name', 'Role', 'Tasks Completed', 'Completion Rate', 'Productivity Score'];
    } else if (reportData.teams) {
        dataArray = reportData.teams;
        headers = ['Project', 'Team Size', 'Productivity', 'Efficiency'];
    } else if (reportData.task_details) {
        dataArray = reportData.task_details;
        headers = ['Task Title', 'Status', 'Priority', 'Project', 'Assigned To'];
    }
    
    // Create header row
    if (headers.length > 0) {
        const headerRow = document.createElement('tr');
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        tableHeader.appendChild(headerRow);
    }
    
    // Create data rows
    dataArray.slice(0, 50).forEach(item => { // Limit to 50 rows for performance
        const row = document.createElement('tr');
        
        // Add cells based on report type
        if (reportData.projects) {
            row.innerHTML = `
                <td>${item.name}</td>
                <td><span class="badge bg-${getStatusColor(item.status)}">${item.status}</span></td>
                <td>${item.completion_rate}%</td>
                <td>${item.total_tasks}</td>
                <td>${item.team_size}</td>
            `;
        } else if (reportData.users) {
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.role}</td>
                <td>${item.tasks_completed}</td>
                <td>${item.completion_rate}%</td>
                <td>${item.productivity_score}</td>
            `;
        }
        // Add more conditions for other report types
        
        tableBody.appendChild(row);
    });
}

function getStatusColor(status) {
    const colors = {
        'active': 'success',
        'completed': 'primary',
        'on_hold': 'warning',
        'planning': 'info'
    };
    return colors[status] || 'secondary';
}

function exportCurrentReport() {
    if (!currentReportData || !currentReportType) {
        showAlert('No report data to export', 'warning');
        return;
    }
    
    const format = document.getElementById('export-format').value;
    const dateRange = document.getElementById('date-range').value;
    const filters = collectFilters();
    
    // Create export request
    const exportData = {
        report_type: currentReportType,
        format: format,
        date_range: dateRange,
        filters: filters
    };
    
    // Create form and submit for download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/reports/advanced/export';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);
    
    // Add export data
    Object.entries(exportData).forEach(([key, value]) => {
        const input = document.createElement('input');
        input.name = key;
        input.value = typeof value === 'object' ? JSON.stringify(value) : value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function scheduleReport() {
    showAlert('Report scheduling feature coming soon!', 'info');
}

function generateQuickReport(templateType) {
    showAlert(`Generating ${templateType.replace('_', ' ')} report...`, 'info');
    // Would implement quick report generation
}

function openTemplateManager() {
    const modal = new bootstrap.Modal(document.getElementById('templateModal'));
    
    // Load templates
    fetch('/admin/reports/advanced/templates')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTemplates(data.templates);
            }
        })
        .catch(error => console.error('Error loading templates:', error));
    
    modal.show();
}

function displayTemplates(templates) {
    const container = document.getElementById('template-list');
    container.innerHTML = '';
    
    Object.entries(templates).forEach(([key, template]) => {
        const templateCard = document.createElement('div');
        templateCard.className = 'card mb-3';
        templateCard.innerHTML = `
            <div class="card-body">
                <h6 class="card-title">${template.name}</h6>
                <p class="card-text text-muted">${template.description}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Schedule: ${template.schedule}</small>
                    <button class="btn btn-sm btn-primary" onclick="useTemplate('${key}')">
                        Use Template
                    </button>
                </div>
            </div>
        `;
        container.appendChild(templateCard);
    });
}

function useTemplate(templateKey) {
    showAlert(`Using template: ${templateKey}`, 'info');
    // Would implement template usage
}

function openScheduledReports() {
    showAlert('Scheduled reports feature coming soon!', 'info');
}

function refreshRecentReports() {
    showAlert('Refreshing recent reports...', 'info');
    // Would implement recent reports refresh
}

function resetModalState() {
    document.getElementById('report-config').style.display = 'block';
    document.getElementById('report-progress').style.display = 'none';
    document.getElementById('report-content').style.display = 'none';
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush