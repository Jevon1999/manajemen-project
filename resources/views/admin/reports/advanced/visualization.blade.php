@extends('layout.app')

@section('title', 'Report Visualization Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Report Visualization Dashboard</h1>
        <div>
            <button class="btn btn-primary" onclick="createCustomDashboard()">
                <i class="fas fa-plus"></i> Create Custom Dashboard
            </button>
            <button class="btn btn-success" onclick="exportDashboard()">
                <i class="fas fa-download"></i> Export Dashboard
            </button>
        </div>
    </div>

    <!-- Dashboard Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dashboard Controls</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="dashboard-timeframe" class="form-label">Time Frame</label>
                            <select class="form-select" id="dashboard-timeframe">
                                <option value="real-time">Real-time</option>
                                <option value="24h">Last 24 Hours</option>
                                <option value="7d" selected>Last 7 Days</option>
                                <option value="30d">Last 30 Days</option>
                                <option value="90d">Last 90 Days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dashboard-refresh" class="form-label">Auto Refresh</label>
                            <select class="form-select" id="dashboard-refresh">
                                <option value="0">Manual</option>
                                <option value="30">30 seconds</option>
                                <option value="60" selected>1 minute</option>
                                <option value="300">5 minutes</option>
                                <option value="600">10 minutes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dashboard-layout" class="form-label">Layout</label>
                            <select class="form-select" id="dashboard-layout">
                                <option value="grid" selected>Grid Layout</option>
                                <option value="compact">Compact View</option>
                                <option value="detailed">Detailed View</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-info" onclick="refreshDashboard()">
                                    <i class="fas fa-sync-alt"></i> Refresh Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Key Performance Indicators</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="kpi-container">
                        <!-- KPI cards will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Visualizations -->
    <div class="row mb-4">
        <!-- Project Progress Overview -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Project Progress Overview</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="configureChart('project-progress')">Configure</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportChart('project-progress')">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="project-progress-chart" width="400" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Team Productivity Trends -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Team Productivity Trends</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="configureChart('team-productivity')">Configure</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportChart('team-productivity')">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="team-productivity-chart" width="400" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Visualizations -->
    <div class="row mb-4">
        <!-- Task Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Task Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="task-distribution-chart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Resource Utilization -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Resource Utilization</h6>
                </div>
                <div class="card-body">
                    <canvas id="resource-utilization-chart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div id="performance-metrics">
                        <!-- Performance metrics will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Data Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-secondary">Interactive Data Explorer</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleAdvancedFilters()">
                            <i class="fas fa-filter"></i> Advanced Filters
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportTableData()">
                            <i class="fas fa-table"></i> Export Data
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Advanced Filters -->
                    <div class="collapse mb-3" id="advanced-filters">
                        <div class="card card-body bg-light">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Data Source</label>
                                    <select class="form-select" id="data-source">
                                        <option value="projects">Projects</option>
                                        <option value="tasks">Tasks</option>
                                        <option value="users">Users</option>
                                        <option value="time-logs">Time Logs</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status Filter</label>
                                    <select class="form-select" id="status-filter" multiple>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="planning">Planning</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <input type="date" class="form-control" id="date-from">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <input type="date" class="form-control" id="date-to">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button class="btn btn-primary" onclick="applyAdvancedFilters()">
                                        Apply Filters
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="data-explorer-table">
                            <thead id="explorer-table-header">
                                <!-- Headers will be populated dynamically -->
                            </thead>
                            <tbody id="explorer-table-body">
                                <!-- Data will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Table pagination">
                        <ul class="pagination justify-content-center" id="table-pagination">
                            <!-- Pagination will be populated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Configuration Modal -->
<div class="modal fade" id="chartConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chart Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="chart-config-content">
                    <!-- Configuration options will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveChartConfig()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let dashboardCharts = {};
let refreshInterval = null;
let currentDataSource = 'projects';

// Initialize dashboard after loading Chart.js via Vite
document.addEventListener('DOMContentLoaded', function() {
    Promise.all([
        import('chart.js/auto').catch(err => { console.warn('Chart import failed', err); return null; }),
        import('chartjs-adapter-date-fns').catch(() => null)
    ]).then(([chartModule]) => {
        if (chartModule && chartModule.default) {
            // expose Chart globally so existing inline initializers keep working
            window.Chart = chartModule.default;
        }
    }).finally(() => {
        initializeDashboard();
        setupAutoRefresh();
    });
});

function initializeDashboard() {
    loadKPIData();
    initializeCharts();
    loadTableData();
}

function loadKPIData() {
    // Simulate KPI data loading
    const kpis = [
        { title: 'Active Projects', value: 24, change: '+12%', color: 'primary' },
        { title: 'Team Members', value: 157, change: '+5%', color: 'success' },
        { title: 'Completed Tasks', value: 892, change: '+18%', color: 'info' },
        { title: 'On-Time Delivery', value: '94%', change: '+2%', color: 'warning' }
    ];

    const container = document.getElementById('kpi-container');
    container.innerHTML = '';

    kpis.forEach(kpi => {
        const kpiCard = document.createElement('div');
        kpiCard.className = 'col-lg-3 col-md-6 mb-3';
        kpiCard.innerHTML = `
            <div class="card border-left-${kpi.color} h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-${kpi.color} text-uppercase mb-1">
                                ${kpi.title}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${kpi.value}
                            </div>
                            <div class="text-xs text-success">
                                ${kpi.change} from last period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(kpiCard);
    });
}

function initializeCharts() {
    // Project Progress Chart
    const projectProgressCtx = document.getElementById('project-progress-chart').getContext('2d');
    dashboardCharts.projectProgress = new Chart(projectProgressCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Projects Completed',
                data: [12, 19, 8, 15, 20, 14],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Projects Started',
                data: [8, 11, 13, 15, 12, 18],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Team Productivity Chart
    const teamProductivityCtx = document.getElementById('team-productivity-chart').getContext('2d');
    dashboardCharts.teamProductivity = new Chart(teamProductivityCtx, {
        type: 'bar',
        data: {
            labels: ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'],
            datasets: [{
                label: 'Tasks Completed',
                data: [65, 78, 45, 89, 67],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Task Distribution Chart
    const taskDistributionCtx = document.getElementById('task-distribution-chart').getContext('2d');
    dashboardCharts.taskDistribution = new Chart(taskDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['To Do', 'In Progress', 'Review', 'Done'],
            datasets: [{
                data: [23, 45, 12, 67],
                backgroundColor: [
                    'rgba(255, 206, 84, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Resource Utilization Chart
    const resourceUtilizationCtx = document.getElementById('resource-utilization-chart').getContext('2d');
    dashboardCharts.resourceUtilization = new Chart(resourceUtilizationCtx, {
        type: 'radar',
        data: {
            labels: ['Developers', 'Designers', 'Managers', 'QA', 'DevOps'],
            datasets: [{
                label: 'Current Utilization',
                data: [85, 70, 65, 90, 75],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Load Performance Metrics
    loadPerformanceMetrics();
}

function loadPerformanceMetrics() {
    const container = document.getElementById('performance-metrics');
    container.innerHTML = `
        <div class="progress mb-3">
            <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                Overall Performance: 85%
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <small class="text-muted">Quality Score</small>
                <div class="font-weight-bold">92%</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Efficiency</small>
                <div class="font-weight-bold">88%</div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-6">
                <small class="text-muted">On-Time Delivery</small>
                <div class="font-weight-bold">94%</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Client Satisfaction</small>
                <div class="font-weight-bold">96%</div>
            </div>
        </div>
    `;
}

function loadTableData() {
    // Simulate loading table data
    const sampleData = [
        { name: 'Project Alpha', status: 'Active', progress: '75%', team: 8, deadline: '2025-01-15' },
        { name: 'Project Beta', status: 'Completed', progress: '100%', team: 6, deadline: '2024-12-20' },
        { name: 'Project Gamma', status: 'Planning', progress: '15%', team: 4, deadline: '2025-03-01' }
    ];

    updateTableWithData(sampleData);
}

function updateTableWithData(data) {
    const header = document.getElementById('explorer-table-header');
    const body = document.getElementById('explorer-table-body');
    
    // Clear existing content
    header.innerHTML = '';
    body.innerHTML = '';

    if (data.length === 0) {
        body.innerHTML = '<tr><td colspan="100%" class="text-center">No data available</td></tr>';
        return;
    }

    // Create headers
    const headers = Object.keys(data[0]);
    const headerRow = document.createElement('tr');
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header.replace('_', ' ').toUpperCase();
        th.style.cursor = 'pointer';
        th.onclick = () => sortTable(header);
        headerRow.appendChild(th);
    });
    header.appendChild(headerRow);

    // Create data rows
    data.forEach(row => {
        const tr = document.createElement('tr');
        headers.forEach(header => {
            const td = document.createElement('td');
            if (header === 'status') {
                td.innerHTML = `<span class="badge bg-${getStatusColor(row[header])}">${row[header]}</span>`;
            } else {
                td.textContent = row[header];
            }
            tr.appendChild(td);
        });
        body.appendChild(tr);
    });
}

function setupAutoRefresh() {
    const refreshSelect = document.getElementById('dashboard-refresh');
    
    refreshSelect.addEventListener('change', function() {
        const interval = parseInt(this.value);
        
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
        
        if (interval > 0) {
            refreshInterval = setInterval(refreshDashboard, interval * 1000);
        }
    });
}

function refreshDashboard() {
    console.log('Refreshing dashboard...');
    loadKPIData();
    // Refresh chart data
    updateChartData();
    loadTableData();
    
    showAlert('Dashboard refreshed successfully', 'success');
}

function updateChartData() {
    // Update charts with new data
    Object.values(dashboardCharts).forEach(chart => {
        // Simulate new data
        if (chart.data.datasets) {
            chart.data.datasets.forEach(dataset => {
                dataset.data = dataset.data.map(() => Math.floor(Math.random() * 100));
            });
            chart.update();
        }
    });
}

function configureChart(chartType) {
    const modal = new bootstrap.Modal(document.getElementById('chartConfigModal'));
    
    // Populate configuration options based on chart type
    const configContent = document.getElementById('chart-config-content');
    configContent.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Chart Type</label>
            <select class="form-select" id="config-chart-type">
                <option value="line">Line Chart</option>
                <option value="bar">Bar Chart</option>
                <option value="doughnut">Doughnut Chart</option>
                <option value="radar">Radar Chart</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Data Source</label>
            <select class="form-select" id="config-data-source">
                <option value="projects">Projects</option>
                <option value="tasks">Tasks</option>
                <option value="users">Users</option>
                <option value="time-logs">Time Logs</option>
            </select>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="config-animations" checked>
                <label class="form-check-label" for="config-animations">
                    Enable Animations
                </label>
            </div>
        </div>
    `;
    
    modal.show();
}

function saveChartConfig() {
    const chartType = document.getElementById('config-chart-type').value;
    const dataSource = document.getElementById('config-data-source').value;
    const animations = document.getElementById('config-animations').checked;
    
    console.log('Saving chart config:', { chartType, dataSource, animations });
    showAlert('Chart configuration saved', 'success');
    
    bootstrap.Modal.getInstance(document.getElementById('chartConfigModal')).hide();
}

function exportChart(chartType) {
    if (dashboardCharts[chartType.replace('-', '')]) {
        const chart = dashboardCharts[chartType.replace('-', '')];
        const url = chart.toBase64Image();
        
        // Create download link
        const link = document.createElement('a');
        link.download = `${chartType}-chart.png`;
        link.href = url;
        link.click();
        
        showAlert('Chart exported successfully', 'success');
    }
}

function createCustomDashboard() {
    showAlert('Custom dashboard creation feature coming soon!', 'info');
}

function exportDashboard() {
    showAlert('Dashboard export feature coming soon!', 'info');
}

function toggleAdvancedFilters() {
    const filtersCollapse = new bootstrap.Collapse(document.getElementById('advanced-filters'));
    filtersCollapse.toggle();
}

function applyAdvancedFilters() {
    const dataSource = document.getElementById('data-source').value;
    const statusFilter = Array.from(document.getElementById('status-filter').selectedOptions).map(option => option.value);
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    console.log('Applying filters:', { dataSource, statusFilter, dateFrom, dateTo });
    
    // Simulate filtered data loading
    loadTableData();
    showAlert('Filters applied successfully', 'success');
}

function resetFilters() {
    document.getElementById('data-source').value = 'projects';
    document.getElementById('status-filter').selectedIndex = -1;
    document.getElementById('date-from').value = '';
    document.getElementById('date-to').value = '';
    
    loadTableData();
    showAlert('Filters reset', 'info');
}

function exportTableData() {
    showAlert('Table data export feature coming soon!', 'info');
}

function sortTable(column) {
    console.log('Sorting by:', column);
    showAlert(`Sorting by ${column}`, 'info');
}

function getStatusColor(status) {
    const colors = {
        'Active': 'success',
        'Completed': 'primary',
        'Planning': 'warning',
        'On Hold': 'secondary'
    };
    return colors[status] || 'secondary';
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
    }, 3000);
}
</script>
@endpush