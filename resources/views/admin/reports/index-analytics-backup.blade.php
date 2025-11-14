@extends('layout.app')

@section('title', 'Admin Reports Dashboard')

@push('styles')
{{-- Chart styles are provided by bundled Chart.js via Vite --}}
<style>
    .stats-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .stats-card.primary { border-left-color: #3b82f6; }
    .stats-card.success { border-left-color: #10b981; }
    .stats-card.warning { border-left-color: #f59e0b; }
    .stats-card.info { border-left-color: #06b6d4; }
    .stats-card.danger { border-left-color: #ef4444; }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 2rem;
    }
    
    .activity-item {
        border-left: 3px solid #e5e7eb;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f9fafb;
        border-radius: 0 6px 6px 0;
    }
    
    .activity-item.success { border-left-color: #10b981; }
    .activity-item.info { border-left-color: #06b6d4; }
    .activity-item.warning { border-left-color: #f59e0b; }
    
    .report-nav {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .report-nav h1 {
        color: white;
        margin-bottom: 0.5rem;
    }
    
    .report-nav .nav-pills .nav-link {
        color: rgba(255,255,255,0.8);
        border-radius: 25px;
        padding: 0.75rem 1.5rem;
        margin-right: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .report-nav .nav-pills .nav-link:hover,
    .report-nav .nav-pills .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .metric-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .metric-card .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .metric-card .metric-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="report-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line me-3"></i>
                    Reports Dashboard
                </h1>
                <p class="mb-0 opacity-75">Comprehensive system analytics and insights</p>
            </div>
            <div class="col-md-6">
                <ul class="nav nav-pills justify-content-end mb-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('reports.index') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.projects') }}">
                            <i class="fas fa-folder me-1"></i>Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.users') }}">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.performance') }}">
                            <i class="fas fa-chart-bar me-1"></i>Performance
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overview['total_projects'] }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $overview['this_month_projects'] }} this month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overview['active_projects'] }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-check me-1"></i>
                                {{ $overview['completed_projects'] }} completed
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $overview['total_users'] }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-user-check me-1"></i>
                                {{ $overview['active_users'] }} active
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Hours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($overview['total_hours'], 1) }}h
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-clock me-1"></i>
                                {{ number_format($overview['this_month_hours'], 1) }}h this month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Projects Timeline Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        Projects Timeline (Last 30 Days)
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectsTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>
                        Task Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="tasksChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row mb-4">
        <!-- Project Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-folder-open me-2"></i>
                        Project Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">By Status</h6>
                            @foreach($projects['by_status'] as $status => $count)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">By Category</h6>
                            @foreach(array_slice($projects['by_category'], 0, 5, true) as $category => $count)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-capitalize">{{ str_replace('_', ' ', $category) }}</span>
                                    <span class="badge bg-info">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i>
                        User Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">By Role</h6>
                            @foreach($users['by_role'] as $role => $count)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-capitalize">{{ $role }}</span>
                                    <span class="badge bg-success">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold">Most Active Users</h6>
                            @foreach($users['most_active']->take(5) as $user)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $user->name }}</span>
                                    <span class="badge bg-warning">{{ number_format($user->total_hours, 1) }}h</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Recent Activities
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($activities as $activity)
                        <div class="activity-item {{ $activity['color'] }}">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="{{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $activity['description'] }}</div>
                                    <small class="text-muted">
                                        by {{ $activity['user'] }} • {{ $activity['date']->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No recent activities found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-rocket me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('reports.projects') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Detailed Project Reports
                        </a>
                        <a href="{{ route('reports.users') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-chart me-2 text-success"></i>
                            User Performance Reports
                        </a>
                        <a href="{{ route('reports.time-tracking') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-clock me-2 text-info"></i>
                            Time Tracking Reports
                        </a>
                        <a href="{{ route('reports.performance') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2 text-warning"></i>
                            Performance Analytics
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-download me-2 text-danger"></i>
                            Export All Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamically import Chart.js via Vite bundle
    import('chart.js/auto').then(({ default: Chart }) => {
        // Projects Timeline Chart
        const timelineEl = document.getElementById('projectsTimelineChart');
        if (timelineEl) {
            const timelineCtx = timelineEl.getContext('2d');
            new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: @json($charts['projects_timeline']['labels']),
                    datasets: [{
                        label: 'Projects Created',
                        data: @json($charts['projects_timeline']['data']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }

        // Tasks Distribution Chart
        const tasksEl = document.getElementById('tasksChart');
        if (tasksEl) {
            const tasksCtx = tasksEl.getContext('2d');
            new Chart(tasksCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($charts['tasks_completion']['labels']),
                    datasets: [{
                        data: @json($charts['tasks_completion']['data']),
                        backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
    }).catch(err => { console.warn('Chart.js import failed', err); });
});
</script>
@endpush