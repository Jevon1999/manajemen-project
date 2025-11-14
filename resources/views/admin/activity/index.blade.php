@extends('layout.app')

@section('title', 'User Activity Tracking')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">User Activity Tracking & Analytics</h1>
        <div>
            <button class="btn btn-primary" onclick="exportActivityData()">
                <i class="fas fa-download"></i> Export Data
            </button>
            <button class="btn btn-warning" onclick="viewSecurityAlerts()">
                <i class="fas fa-shield-alt"></i> Security Alerts
            </button>
        </div>
    </div>

    <!-- Activity Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Users Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activitySummary['total_users_today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activities Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activitySummary['total_activities_today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Session Duration</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activitySummary['average_session_duration'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Security Alerts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activitySummary['security_alerts'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Analytics Dashboard</h6>
                </div>
                <div class="card-body">
                    <!-- Analytics Controls -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="analytics-type" class="form-label">Analytics Type</label>
                            <select class="form-select" id="analytics-type" onchange="loadAnalytics()">
                                <option value="overview">Overview</option>
                                <option value="behavioral">Behavioral Analysis</option>
                                <option value="security">Security Analysis</option>
                                <option value="performance">Performance Analysis</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="analytics-range" class="form-label">Time Range</label>
                            <select class="form-select" id="analytics-range" onchange="loadAnalytics()">
                                <option value="24_hours">Last 24 Hours</option>
                                <option value="7_days" selected>Last 7 Days</option>
                                <option value="30_days">Last 30 Days</option>
                                <option value="90_days">Last 90 Days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="real-time-toggle" class="form-label">Real-time Updates</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="real-time-toggle" onchange="toggleRealTime()">
                                <label class="form-check-label" for="real-time-toggle">Enable</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-info" onclick="refreshAnalytics()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Content -->
                    <div id="analytics-content">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading analytics...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Top Users -->
    <div class="row mb-4">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">Recent Activities</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="filterActivities()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportActivities()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Resource</th>
                                    <th>Time</th>
                                    <th>Risk</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity['user_name'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $activity['action'] }}</span>
                                    </td>
                                    <td>{{ $activity['resource'] }}</td>
                                    <td>{{ $activity['timestamp'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $activity['risk_level'] === 'high' ? 'danger' : ($activity['risk_level'] === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($activity['risk_level']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewActivityDetails({{ $activity['id'] }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Active Users -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Top Active Users</h6>
                </div>
                <div class="card-body">
                    @foreach($topActiveUsers as $user)
                    <div class="d-flex align-items-center mb-3 p-2 border rounded">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $user['name'] }}</h6>
                            <small class="text-muted">{{ $user['activity_count'] }} activities</small>
                            <div class="mt-1">
                                <small class="text-success">Risk Score: {{ $user['risk_score'] }}</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="viewUserActivity({{ $user['id'] }})">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Heatmap and Patterns -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Activity Heatmap</h6>
                </div>
                <div class="card-body">
                    <canvas id="activity-heatmap-chart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Behavioral Patterns</h6>
                </div>
                <div class="card-body">
                    <div id="behavioral-patterns">
                        <div class="mb-3">
                            <h6>Peak Activity Hours</h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary">9:00-11:00</span>
                                <span class="badge bg-primary">14:00-16:00</span>
                                <span class="badge bg-primary">19:00-21:00</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Common User Flows</h6>
                            <ul class="list-unstyled">
                                <li><small>Login → Dashboard → Projects (45%)</small></li>
                                <li><small>Login → Tasks → Updates (30%)</small></li>
                                <li><small>Login → Reports → Export (25%)</small></li>
                            </ul>
                        </div>
                        <div>
                            <h6>Session Distribution</h6>
                            <div class="progress mb-1">
                                <div class="progress-bar bg-success" style="width: 30%">0-15min</div>
                                <div class="progress-bar bg-info" style="width: 45%">15-30min</div>
                                <div class="progress-bar bg-warning" style="width: 20%">30-60min</div>
                                <div class="progress-bar bg-danger" style="width: 5%">60+min</div>
                            </div>
                            <small class="text-muted">Session duration distribution</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Activity Detail Modal -->
<div class="modal fade" id="userActivityModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="user-activity-content">
                    <!-- User activity details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Security Alerts Modal -->
<div class="modal fade" id="securityAlertsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Security Alerts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="security-alerts-content">
                    <!-- Security alerts will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Activities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <select class="form-select" id="filter-user">
                        <option value="">All Users</option>
                        <!-- Users will be populated -->
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Action Type</label>
                    <select class="form-select" id="filter-action" multiple>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Risk Level</label>
                    <select class="form-select" id="filter-risk">
                        <option value="">All Levels</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date Range</label>
                    <div class="row">
                        <div class="col-6">
                            <input type="date" class="form-control" id="filter-date-from">
                        </div>
                        <div class="col-6">
                            <input type="date" class="form-control" id="filter-date-to">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyActivityFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let realTimeUpdates = false;
let updateInterval = null;
let analyticsChart = null;

// Initialize page: dynamically import Chart.js via Vite then run initializers
document.addEventListener('DOMContentLoaded', function() {
    import('chart.js/auto').then(({ default: Chart }) => { window.Chart = Chart; }).catch(err => { console.warn('Chart import failed', err); }).finally(() => {
        loadAnalytics();
        initializeHeatmap();
    });
});

function loadAnalytics() {
    const type = document.getElementById('analytics-type').value;
    const range = document.getElementById('analytics-range').value;
    
    const analyticsContent = document.getElementById('analytics-content');
    analyticsContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    fetch(`/admin/activity/analytics?type=${type}&range=${range}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnalytics(data.analytics, type);
            } else {
                showAlert('Failed to load analytics', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
            showAlert('Error loading analytics', 'error');
        });
}

function displayAnalytics(analytics, type) {
    const container = document.getElementById('analytics-content');
    
    switch(type) {
        case 'overview':
            displayOverviewAnalytics(analytics, container);
            break;
        case 'behavioral':
            displayBehavioralAnalytics(analytics, container);
            break;
        case 'security':
            displaySecurityAnalytics(analytics, container);
            break;
        case 'performance':
            displayPerformanceAnalytics(analytics, container);
            break;
    }
}

function displayOverviewAnalytics(analytics, container) {
    container.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <h6>User Engagement</h6>
                        <p>Daily Active Users: <strong>${analytics.user_engagement.daily_active_users}</strong></p>
                        <p>Weekly Active Users: <strong>${analytics.user_engagement.weekly_active_users}</strong></p>
                        <p>Monthly Active Users: <strong>${analytics.user_engagement.monthly_active_users}</strong></p>
                        <p>Retention Rate: <strong>${analytics.user_engagement.user_retention_rate}</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <h6>Feature Usage</h6>
                        <div class="mb-2">Project Management: <strong>${analytics.feature_usage.most_used_features.project_management}%</strong></div>
                        <div class="mb-2">Task Tracking: <strong>${analytics.feature_usage.most_used_features.task_tracking}%</strong></div>
                        <div class="mb-2">Reporting: <strong>${analytics.feature_usage.most_used_features.reporting}%</strong></div>
                        <p>Adoption Rate: <strong>${analytics.feature_usage.feature_adoption_rate}</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-info">
                    <div class="card-body">
                        <h6>Performance Metrics</h6>
                        <p>Page Load Time: <strong>${analytics.performance_metrics.average_page_load_time}</strong></p>
                        <p>Error Rate: <strong>${analytics.performance_metrics.error_rate}</strong></p>
                        <p>Satisfaction Score: <strong>${analytics.performance_metrics.user_satisfaction_score}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function displayBehavioralAnalytics(analytics, container) {
    container.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Usage Patterns</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Peak Hours:</strong> ${analytics.usage_patterns.peak_hours.join(', ')}</p>
                        <p><strong>Common Workflows:</strong></p>
                        <ul>
                            ${analytics.usage_patterns.common_workflows.map(workflow => `<li>${workflow}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>User Journey</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Top Entry Points:</strong></p>
                        <ul>
                            ${Object.entries(analytics.user_journey.entry_points).map(([point, percentage]) => 
                                `<li>${point}: ${percentage}%</li>`
                            ).join('')}
                        </ul>
                        <p><strong>Common Exit Points:</strong></p>
                        <ul>
                            ${Object.entries(analytics.user_journey.exit_points).map(([point, percentage]) => 
                                `<li>${point}: ${percentage}%</li>`
                            ).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function displaySecurityAnalytics(analytics, container) {
    container.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <h6>Authentication</h6>
                        <p>Failed Logins: <strong>${analytics.authentication.failed_login_attempts}</strong></p>
                        <p>Successful Logins: <strong>${analytics.authentication.successful_logins}</strong></p>
                        <p>Password Resets: <strong>${analytics.authentication.password_resets}</strong></p>
                        <p>MFA Usage: <strong>${analytics.authentication.mfa_usage_rate}</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-danger">
                    <div class="card-body">
                        <h6>Access Patterns</h6>
                        <p>Unusual Access: <strong>${analytics.access_patterns.unusual_access_times}</strong></p>
                        <p>Suspicious IPs: <strong>${analytics.access_patterns.suspicious_ip_addresses}</strong></p>
                        <p>Privilege Escalations: <strong>${analytics.access_patterns.privilege_escalations}</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-dark">
                    <div class="card-body">
                        <h6>Threat Indicators</h6>
                        <p>Brute Force: <strong>${analytics.threat_indicators.brute_force_attempts}</strong></p>
                        <p>Data Exfiltration: <strong>${analytics.threat_indicators.potential_data_exfiltration}</strong></p>
                        <p>Unauthorized Access: <strong>${analytics.threat_indicators.unauthorized_access_attempts}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function displayPerformanceAnalytics(analytics, container) {
    container.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="card border-left-success">
                    <div class="card-body">
                        <h6>System Performance</h6>
                        <p>Response Time: <strong>${analytics.system_performance.average_response_time}</strong></p>
                        <p>DB Query Time: <strong>${analytics.system_performance.database_query_time}</strong></p>
                        <p>Cache Hit Ratio: <strong>${analytics.system_performance.cache_hit_ratio}</strong></p>
                        <p>Error Rate: <strong>${analytics.system_performance.error_rate}</strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-left-info">
                    <div class="card-body">
                        <h6>User Experience</h6>
                        <p>Dashboard Load: <strong>${analytics.user_experience.page_load_times.dashboard}</strong></p>
                        <p>Projects Load: <strong>${analytics.user_experience.page_load_times.projects}</strong></p>
                        <p>Reports Load: <strong>${analytics.user_experience.page_load_times.reports}</strong></p>
                        <p>Bounce Rate: <strong>${analytics.user_experience.bounce_rate}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function initializeHeatmap() {
    const ctx = document.getElementById('activity-heatmap-chart').getContext('2d');
    
    // Sample heatmap data
    const heatmapData = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: '00:00-06:00',
            data: [5, 8, 12, 7, 9, 15, 10],
            backgroundColor: 'rgba(54, 162, 235, 0.2)'
        }, {
            label: '06:00-12:00',
            data: [25, 30, 35, 28, 32, 20, 15],
            backgroundColor: 'rgba(255, 206, 86, 0.6)'
        }, {
            label: '12:00-18:00',
            data: [45, 50, 48, 52, 49, 35, 25],
            backgroundColor: 'rgba(255, 99, 132, 0.8)'
        }, {
            label: '18:00-24:00',
            data: [20, 25, 22, 28, 24, 40, 35],
            backgroundColor: 'rgba(75, 192, 192, 0.6)'
        }]
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: heatmapData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function toggleRealTime() {
    const toggle = document.getElementById('real-time-toggle');
    realTimeUpdates = toggle.checked;
    
    if (realTimeUpdates) {
        updateInterval = setInterval(refreshAnalytics, 30000); // Update every 30 seconds
        showAlert('Real-time updates enabled', 'success');
    } else {
        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }
        showAlert('Real-time updates disabled', 'info');
    }
}

function refreshAnalytics() {
    loadAnalytics();
    showAlert('Analytics refreshed', 'success');
}

function viewUserActivity(userId) {
    const modal = new bootstrap.Modal(document.getElementById('userActivityModal'));
    const content = document.getElementById('user-activity-content');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    fetch(`/admin/activity/user/${userId}?range=30_days`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUserActivityDetails(data);
            } else {
                showAlert('Failed to load user activity', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading user activity:', error);
            showAlert('Error loading user activity', 'error');
        });
    
    modal.show();
}

function displayUserActivityDetails(data) {
    const content = document.getElementById('user-activity-content');
    const user = data.user;
    const activities = data.activities;
    const performance = data.performance;
    
    content.innerHTML = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>User Information</h6>
                <p><strong>Name:</strong> ${user.name}</p>
                <p><strong>Email:</strong> ${user.email}</p>
                <p><strong>Role:</strong> ${user.role}</p>
                <p><strong>Last Login:</strong> ${user.last_login || 'Never'}</p>
            </div>
            <div class="col-md-6">
                <h6>Performance Metrics</h6>
                <p><strong>Productivity Score:</strong> ${performance.productivity_score}</p>
                <p><strong>Tasks Completed:</strong> ${performance.tasks_completed}</p>
                <p><strong>Collaboration Score:</strong> ${performance.collaboration_score}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Recent Activities</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>Timestamp</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${activities.slice(0, 20).map(activity => `
                                <tr>
                                    <td>${activity.action}</td>
                                    <td>${activity.resource}</td>
                                    <td>${activity.timestamp}</td>
                                    <td>${activity.ip_address}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function viewSecurityAlerts() {
    const modal = new bootstrap.Modal(document.getElementById('securityAlertsModal'));
    const content = document.getElementById('security-alerts-content');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    fetch('/admin/activity/security-alerts?severity=all&range=7_days')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySecurityAlerts(data.alerts);
            } else {
                showAlert('Failed to load security alerts', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading security alerts:', error);
            showAlert('Error loading security alerts', 'error');
        });
    
    modal.show();
}

function displaySecurityAlerts(alerts) {
    const content = document.getElementById('security-alerts-content');
    
    if (alerts.length === 0) {
        content.innerHTML = '<div class="text-center text-muted"><p>No security alerts found.</p></div>';
        return;
    }
    
    content.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>User</th>
                        <th>Timestamp</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${alerts.map(alert => `
                        <tr>
                            <td>${alert.type}</td>
                            <td><span class="badge bg-${alert.severity === 'high' ? 'danger' : (alert.severity === 'medium' ? 'warning' : 'info')}">${alert.severity}</span></td>
                            <td>${alert.user_name}</td>
                            <td>${alert.timestamp}</td>
                            <td><span class="badge bg-${alert.status === 'resolved' ? 'success' : 'warning'}">${alert.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="acknowledgeAlert(${alert.id})">
                                    <i class="fas fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function acknowledgeAlert(alertId) {
    fetch('/admin/activity/acknowledge-alert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            alert_id: alertId,
            notes: 'Alert acknowledged by admin'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Alert acknowledged successfully', 'success');
            viewSecurityAlerts(); // Refresh alerts
        } else {
            showAlert('Failed to acknowledge alert', 'error');
        }
    })
    .catch(error => {
        console.error('Error acknowledging alert:', error);
        showAlert('Error acknowledging alert', 'error');
    });
}

function filterActivities() {
    const modal = new bootstrap.Modal(document.getElementById('filterModal'));
    modal.show();
}

function applyActivityFilters() {
    const filters = {
        user: document.getElementById('filter-user').value,
        action: Array.from(document.getElementById('filter-action').selectedOptions).map(option => option.value),
        risk: document.getElementById('filter-risk').value,
        date_from: document.getElementById('filter-date-from').value,
        date_to: document.getElementById('filter-date-to').value
    };
    
    console.log('Applying filters:', filters);
    showAlert('Filters applied successfully', 'success');
    
    bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
    
    // Would reload activities with filters
    location.reload();
}

function exportActivityData() {
    showAlert('Exporting activity data...', 'info');
    
    // Create export form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/activity/export';
    form.style.display = 'none';
    
    const csrfInput = document.createElement('input');
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);
    
    const formatInput = document.createElement('input');
    formatInput.name = 'format';
    formatInput.value = 'csv';
    form.appendChild(formatInput);
    
    const rangeInput = document.createElement('input');
    rangeInput.name = 'range';
    rangeInput.value = '30_days';
    form.appendChild(rangeInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportActivities() {
    exportActivityData();
}

function viewActivityDetails(activityId) {
    showAlert(`Viewing details for activity ${activityId}`, 'info');
    // Would open detailed activity view
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