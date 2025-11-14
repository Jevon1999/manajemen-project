@extends('layout.app')

@section('title', 'Analytics & Reports - Admin')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    body {
        background: #f8fafc;
    }
    
    /* Modern Header */
    .analytics-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    }
    
    .analytics-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }
    
    .analytics-header h1 {
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        color: white;
        letter-spacing: -1px;
        position: relative;
        z-index: 2;
    }
    
    .analytics-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
        font-weight: 400;
        position: relative;
        z-index: 2;
    }
    
    /* Glass Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }
    
    /* Modern Stat Cards */
    .stat-card {
        border-radius: 20px;
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        height: 100%;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: pulse 3s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
    }
    
    .stat-card.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-card.orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    
    .stat-card .icon {
        font-size: 3rem;
        opacity: 0.25;
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .stat-card .value {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        position: relative;
        z-index: 2;
    }
    
    .stat-card .label {
        font-size: 1rem;
        opacity: 0.95;
        font-weight: 500;
        position: relative;
        z-index: 2;
    }
    
    /* Report Type Cards */
    .report-type-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .report-type-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    
    .report-type-card:hover {
        transform: translateY(-8px);
        border-color: #667eea;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
    }
    
    .report-type-card:hover::before {
        transform: scaleX(1);
    }
    
    .report-type-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .report-type-card:hover .icon-wrapper {
        transform: scale(1.15) rotate(5deg);
    }
    
    .report-type-card.monthly .icon-wrapper {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .report-type-card.yearly .icon-wrapper {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .report-type-card.project .icon-wrapper {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }
    
    /* Modern Table */
    .modern-table {
        border-radius: 0;
        overflow: hidden;
    }
    
    .modern-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1.2px;
        padding: 1.25rem 1rem;
        border: none;
    }
    
    .modern-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .modern-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
    }
    
    .modern-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border: none;
        font-weight: 500;
    }
    
    /* Modern Badges */
    .modern-badge {
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }
    
    .modern-badge.success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .modern-badge.warning {
        background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
        color: white;
    }
    
    .modern-badge.danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
    }
    
    /* Modern Buttons */
    .btn-modern {
        border-radius: 14px;
        padding: 0.875rem 1.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }
    
    .btn-modern-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-modern-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .btn-modern-outline {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
    }
    
    .btn-modern-outline:hover {
        background: #667eea;
        color: white;
    }
    
    /* Filter Form */
    .filter-form {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .filter-form .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-form .form-control,
    .filter-form .form-select {
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        padding: 0.875rem 1.125rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .filter-form .form-control:focus,
    .filter-form .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    /* Section Headers */
    .section-header {
        font-weight: 700;
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    
    .empty-state .icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        opacity: 0.4;
    }
    
    .empty-state h3 {
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 1.25rem;
    }
    
    .empty-state p {
        color: #9ca3af;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Modern Header -->
    <div class="analytics-header">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <h1>
                    @if($reportType == 'monthly')
                        📅 Monthly Analytics
                    @elseif($reportType == 'yearly')
                        📊 Yearly Performance
                    @elseif($reportType == 'project')
                        📁 Project Insights
                    @else
                        📈 Analytics Dashboard
                    @endif
                </h1>
                <p>
                    @if($reportType == 'monthly')
                        Track your monthly progress and identify trends across time
                    @elseif($reportType == 'yearly')
                        Annual overview of achievements, milestones and growth
                    @elseif($reportType == 'project')
                        Comprehensive breakdown and analytics for all projects
                    @else
                        Generate custom reports with advanced filters and insights
                    @endif
                </p>
            </div>
            <div class="col-lg-3 text-end d-none d-lg-block">
                <div style="font-size: 5rem; opacity: 0.15;">
                    @if($reportType == 'monthly')
                        📅
                    @elseif($reportType == 'yearly')
                        📊
                    @elseif($reportType == 'project')
                        📁
                    @else
                        📈
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card purple">
                <div class="icon">🏗️</div>
                <div class="value">{{ $stats['total_projects'] }}</div>
                <div class="label">Total Projects</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card blue">
                <div class="icon">⚡</div>
                <div class="value">{{ $stats['active_projects'] }}</div>
                <div class="label">Active Projects</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card green">
                <div class="icon">📋</div>
                <div class="value">{{ $stats['total_tasks'] }}</div>
                <div class="label">Total Tasks</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="stat-card orange">
                <div class="icon">✅</div>
                <div class="value">{{ $stats['completed_tasks'] }}</div>
                <div class="label">Completed Tasks</div>
            </div>
        </div>
    </div>

    @if(!$reportType || $reportType == 'general')
        <!-- Report Type Selection -->
        <div class="mb-5">
            <h3 class="section-header">
                <i class="fas fa-th-large"></i>
                Choose Report Type
            </h3>
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="{{ route('admin.reports.index', ['type' => 'monthly']) }}" class="text-decoration-none">
                        <div class="report-type-card monthly">
                            <div class="icon-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4 class="mb-2" style="font-weight: 700; color: #1f2937;">Monthly Reports</h4>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">View detailed monthly performance metrics and identify trends over the last 12 months</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4">
                    <a href="{{ route('admin.reports.index', ['type' => 'yearly']) }}" class="text-decoration-none">
                        <div class="report-type-card yearly">
                            <div class="icon-wrapper">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 class="mb-2" style="font-weight: 700; color: #1f2937;">Yearly Reports</h4>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Analyze yearly achievements and compare performance across the last 5 years</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4">
                    <a href="{{ route('admin.reports.index', ['type' => 'project']) }}" class="text-decoration-none">
                        <div class="report-type-card project">
                            <div class="icon-wrapper">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h4 class="mb-2" style="font-weight: 700; color: #1f2937;">Project Reports</h4>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Deep dive into individual project statistics, team performance and deliverables</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if($reportType == 'monthly')
        <!-- Monthly Reports Content -->
        <div class="glass-card mb-5">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-header mb-0">
                        <i class="fas fa-calendar-alt"></i>
                        Last 12 Months Performance
                    </h3>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-modern btn-modern-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th>MONTH</th>
                                <th class="text-center">TASKS</th>
                                <th class="text-center">COMPLETED</th>
                                <th class="text-center">COMPLETION RATE</th>
                                <th class="text-center">WORK HOURS</th>
                                <th class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $data)
                            <tr>
                                <td><strong style="font-weight: 700;">{{ $data['month'] }}</strong></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">{{ $data['total_tasks'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">{{ $data['completed_tasks'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="modern-badge {{ $data['completion_rate'] >= 75 ? 'success' : ($data['completion_rate'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $data['completion_rate'] }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">⏱️ {{ $data['work_hours'] }}h</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.reports.monthly') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="month" value="{{ $data['month_number'] }}">
                                        <button type="submit" class="btn btn-modern btn-modern-primary btn-sm">
                                            <i class="fas fa-download"></i> Export
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($reportType == 'yearly')
        <!-- Yearly Reports Content -->
        <div class="glass-card mb-5">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-header mb-0">
                        <i class="fas fa-chart-line"></i>
                        Yearly Performance Overview
                    </h3>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-modern btn-modern-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <div class="row g-4">
                    @foreach($yearlyData as $index => $data)
                    <div class="col-lg-6">
                        <div class="glass-card h-100" style="background: linear-gradient(135deg, 
                            {{ $index % 4 === 0 ? '#667eea, #764ba2' : 
                               ($index % 4 === 1 ? '#4facfe, #00f2fe' : 
                               ($index % 4 === 2 ? '#43e97b, #38f9d7' : '#fa709a, #fee140')) }});">
                            <div class="p-4 text-white">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <h2 class="fw-bold mb-0" style="font-size: 2.5rem;">{{ $data['year'] }}</h2>
                                    <span class="modern-badge success">{{ $data['completion_rate'] }}%</span>
                                </div>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                            <small class="d-block opacity-75" style="font-weight: 500;">🏗️ Projects</small>
                                            <h3 class="mb-0 mt-2" style="font-weight: 800;">{{ $data['total_projects'] }}</h3>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                            <small class="d-block opacity-75" style="font-weight: 500;">✅ Completed</small>
                                            <h3 class="mb-0 mt-2" style="font-weight: 800;">{{ $data['completed_projects'] }}</h3>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                            <small class="d-block opacity-75" style="font-weight: 500;">📋 Total Tasks</small>
                                            <h3 class="mb-0 mt-2" style="font-weight: 800;">{{ $data['total_tasks'] }}</h3>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                            <small class="d-block opacity-75" style="font-weight: 500;">✔️ Done Tasks</small>
                                            <h3 class="mb-0 mt-2" style="font-weight: 800;">{{ $data['completed_tasks'] }}</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded" 
                                     style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                    <span style="font-weight: 600; opacity: 0.9;">
                                        <i class="fas fa-clock me-2"></i>Total Work Hours
                                    </span>
                                    <strong style="font-size: 1.75rem; font-weight: 800;">{{ $data['work_hours'] }}h</strong>
                                </div>

                                <form action="{{ route('admin.reports.yearly') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="year" value="{{ $data['year'] }}">
                                    <button type="submit" class="btn w-100" 
                                            style="background: rgba(255,255,255,0.2); 
                                                   border: 2px solid rgba(255,255,255,0.3); 
                                                   color: white; 
                                                   font-weight: 700; 
                                                   padding: 0.875rem;
                                                   backdrop-filter: blur(10px);
                                                   transition: all 0.3s ease;
                                                   font-size: 1rem;">
                                        <i class="fas fa-download me-2"></i>Download Full Report
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($reportType == 'project')
        <!-- Project Reports Content -->
        <div class="glass-card mb-5">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="section-header mb-0">
                        <i class="fas fa-folder-open"></i>
                        All Projects Overview
                    </h3>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-modern btn-modern-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th>PROJECT</th>
                                <th>LEADER</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">TASKS</th>
                                <th class="text-center">COMPLETED</th>
                                <th class="text-center">OVERDUE</th>
                                <th class="text-center">RATE</th>
                                <th class="text-center">HOURS</th>
                                <th class="text-center">TEAM</th>
                                <th class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectData as $data)
                            <tr>
                                <td>
                                    <strong style="font-weight: 700; color: #1a1a1a;">
                                        📁 {{ $data['project_name'] }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark px-3">
                                        <i class="fas fa-user-tie me-1"></i>{{ $data['leader_name'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="modern-badge {{ $data['status'] == 'active' ? 'success' : ($data['status'] == 'completed' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($data['status']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">{{ $data['total_tasks'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">✅ {{ $data['completed_tasks'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($data['overdue_tasks'] > 0)
                                        <span class="modern-badge danger">⚠️ {{ $data['overdue_tasks'] }}</span>
                                    @else
                                        <span class="badge bg-light text-muted px-3">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="modern-badge {{ $data['completion_rate'] >= 75 ? 'success' : ($data['completion_rate'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $data['completion_rate'] }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">⏱️ {{ $data['work_hours'] }}h</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark px-3">👥 {{ $data['team_members'] }}</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.reports.project') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="project_id" value="{{ $data['project_id'] }}">
                                        <button type="submit" class="btn btn-modern btn-modern-primary btn-sm">
                                            <i class="fas fa-download"></i> Export
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Custom Report Form -->
        <div class="col-lg-5">
            <div class="glass-card sticky-top" style="top: 2rem;">
                <div class="p-4">
                    <h3 class="section-header mb-4">
                        <i class="fas fa-sliders-h"></i>
                        Custom Report
                    </h3>
                    
                    <form id="reportForm" action="{{ route('admin.reports.generate') }}" method="POST">
                        @csrf
                        
                        <!-- Date Range -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">
                                📅 Date Range <span class="text-danger">*</span>
                            </label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="date" class="form-control" 
                                           style="border: 2px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-weight: 500;"
                                           name="date_from" 
                                           value="{{ now()->subMonth()->format('Y-m-d') }}" required>
                                    <small class="text-muted" style="font-size: 0.75rem;">Start Date</small>
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control"
                                           style="border: 2px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-weight: 500;"
                                           name="date_to" 
                                           value="{{ now()->format('Y-m-d') }}" required>
                                    <small class="text-muted" style="font-size: 0.75rem;">End Date</small>
                                </div>
                            </div>
                        </div>

                        <!-- Project Filter -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">
                                🏗️ Project <span class="text-muted">(Optional)</span>
                            </label>
                            <select class="form-select" name="project_id"
                                    style="border: 2px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-weight: 500;">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User Filter -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">
                                👤 User <span class="text-muted">(Optional)</span>
                            </label>
                            <select class="form-select" name="user_id"
                                    style="border: 2px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-weight: 500;">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}">
                                        {{ $user->full_name }} - {{ ucfirst($user->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">
                                📊 Status <span class="text-muted">(Optional)</span>
                            </label>
                            <select class="form-select" name="status"
                                    style="border: 2px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-weight: 500;">
                                <option value="">All Statuses</option>
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <!-- Info Box -->
                        <div class="p-3 rounded mb-4" style="background: linear-gradient(135deg, #667eea15, #764ba215); border-left: 4px solid #667eea;">
                            <div style="font-weight: 600; color: #667eea; margin-bottom: 0.5rem;">
                                <i class="fas fa-info-circle me-2"></i>Report Contents
                            </div>
                            <ul class="mb-0 small" style="color: #64748b; line-height: 1.8;">
                                <li>📊 Project Summary & Statistics</li>
                                <li>✅ Task Completion Analysis</li>
                                <li>⏱️ Work Time Tracking Data</li>
                                <li>👥 Team Performance Metrics</li>
                            </ul>
                        </div>

                        <!-- Generate Button -->
                        <button type="submit" class="btn btn-modern btn-modern-primary w-100" id="generateBtn"
                                style="padding: 1rem; font-size: 1rem; font-weight: 700;">
                            <i class="fas fa-download me-2"></i>
                            Generate & Download Report
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Reports History -->
        <div class="col-lg-7">
            <div class="glass-card mb-4">
                <div class="p-4">
                    <h3 class="section-header mb-4">
                        <i class="fas fa-history"></i>
                        Recent Reports
                    </h3>
                    
                    @if($recentReports->count() > 0)
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>TYPE</th>
                                        <th>FILTERS APPLIED</th>
                                        <th class="text-center">GENERATED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReports as $report)
                                        <tr>
                                            <td>
                                                <span class="modern-badge info">
                                                    {{ ucfirst($report->report_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $filters = is_array($report->filters) ? $report->filters : [];
                                                @endphp
                                                <div style="font-size: 0.875rem; color: #64748b; line-height: 1.6;">
                                                    @if(!empty($filters['date_from']) && !empty($filters['date_to']))
                                                        <div>
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <strong>{{ $filters['date_from'] }}</strong> to <strong>{{ $filters['date_to'] }}</strong>
                                                        </div>
                                                    @endif
                                                    @if(!empty($filters['project_id']))
                                                        <div>
                                                            <i class="fas fa-folder me-1"></i>
                                                            {{ $projects->firstWhere('project_id', $filters['project_id'])->project_name ?? 'N/A' }}
                                                        </div>
                                                    @endif
                                                    @if(!empty($filters['user_id']))
                                                        <div>
                                                            <i class="fas fa-user me-1"></i>
                                                            {{ $users->firstWhere('user_id', $filters['user_id'])->full_name ?? 'N/A' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a;">
                                                    {{ $report->generated_at->format('d M Y') }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: #94a3b8;">
                                                    {{ $report->generated_at->format('H:i') }} • {{ $report->generated_at->diffForHumans() }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">📋</div>
                            <p style="font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">No Reports Yet</p>
                            <p style="font-size: 0.875rem; color: #94a3b8;">Generate your first report using the form</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Guide -->
            <div class="glass-card">
                <div class="p-4">
                    <h5 style="font-weight: 700; color: #1a1a1a; margin-bottom: 1.5rem;">
                        <i class="fas fa-lightbulb me-2" style="color: #f59e0b;"></i>
                        Quick Guide
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #667eea15, #764ba215); border-left: 3px solid #667eea;">
                                <div style="font-weight: 600; color: #667eea; margin-bottom: 0.5rem;">
                                    1️⃣ Select Date Range
                                </div>
                                <p class="mb-0 small" style="color: #64748b;">
                                    Choose start and end dates for your report period
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #4facfe15, #00f2fe15); border-left: 3px solid #4facfe;">
                                <div style="font-weight: 600; color: #4facfe; margin-bottom: 0.5rem;">
                                    2️⃣ Apply Filters
                                </div>
                                <p class="mb-0 small" style="color: #64748b;">
                                    Optionally filter by project, user, or status
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #43e97b15, #38f9d715); border-left: 3px solid #43e97b;">
                                <div style="font-weight: 600; color: #43e97b; margin-bottom: 0.5rem;">
                                    3️⃣ Generate Report
                                </div>
                                <p class="mb-0 small" style="color: #64748b;">
                                    Click the button to generate and download CSV
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #fa709a15, #fee14015); border-left: 3px solid #fa709a;">
                                <div style="font-weight: 600; color: #fa709a; margin-bottom: 0.5rem;">
                                    4️⃣ Analyze Data
                                </div>
                                <p class="mb-0 small" style="color: #64748b;">
                                    Open in Excel or Google Sheets for analysis
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
    btn.style.opacity = '0.7';
    
    // Re-enable after 5 seconds in case of error
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download me-2"></i>Generate & Download Report';
        btn.style.opacity = '1';
    }, 5000);
});

// Add hover effect to buttons
document.querySelectorAll('.btn-modern').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>
@endpush
