@extends('layout.app')

@section('title', 'Projects Report')

@push('styles')
<style>
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-item {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #3b82f6;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .project-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .project-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-active { background: #dcfce7; color: #166534; }
    .status-completed { background: #dbeafe; color: #1e40af; }
    .status-planning { background: #fef3c7; color: #92400e; }
    .status-on-hold { background: #fee2e2; color: #991b1b; }
    
    .priority-high { border-left-color: #ef4444; }
    .priority-critical { border-left-color: #dc2626; }
    .priority-medium { border-left-color: #f59e0b; }
    .priority-low { border-left-color: #10b981; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-folder-open me-2 text-primary"></i>
                Projects Report
            </h1>
            <p class="text-muted">Detailed analysis of project performance and statistics</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reports.projects') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="period" class="form-label text-white">Time Period</label>
                    <select name="period" id="period" class="form-select">
                        <option value="7" {{ $filters['period'] == '7' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $filters['period'] == '30' ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $filters['period'] == '90' ? 'selected' : '' }}>Last 3 months</option>
                        <option value="365" {{ $filters['period'] == '365' ? 'selected' : '' }}>Last year</option>
                        <option value="all" {{ $filters['period'] == 'all' ? 'selected' : '' }}>All time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label text-white">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $filters['status'] == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label text-white">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="all" {{ $filters['category'] == 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ $filters['category'] == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-light btn-block">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Projects</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">${{ number_format($stats['total_budget'] ?? 0, 0) }}</div>
            <div class="stat-label">Total Budget</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ number_format($stats['avg_completion'] ?? 0, 1) }}%</div>
            <div class="stat-label">Avg Completion</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['with_templates'] }}</div>
            <div class="stat-label">Using Templates</div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table me-2"></i>
                Projects List ({{ $projects->total() }} projects)
            </h6>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('reports.export', ['type' => 'projects', 'format' => 'excel']) }}">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.export', ['type' => 'projects', 'format' => 'pdf']) }}">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Creator</th>
                            <th>Members</th>
                            <th>Budget</th>
                            <th>Completion</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr class="project-card priority-{{ $project->priority ?? 'medium' }}">
                                <td>
                                    <div class="fw-bold">{{ $project->project_name }}</div>
                                    @if($project->template)
                                        <small class="text-muted">
                                            <i class="fas fa-layer-group me-1"></i>
                                            {{ $project->template->name }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $project->category ?? 'other')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="project-status status-{{ $project->status ?? 'planning' }}">
                                        {{ ucfirst(str_replace('-', ' ', $project->status ?? 'planning')) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityColors = [
                                            'low' => 'success',
                                            'medium' => 'warning', 
                                            'high' => 'danger',
                                            'critical' => 'dark'
                                        ];
                                        $priority = $project->priority ?? 'medium';
                                    @endphp
                                    <span class="badge bg-{{ $priorityColors[$priority] }}">
                                        {{ ucfirst($priority) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($project->creator->name ?? 'Unknown') }}&background=random" 
                                                 class="rounded-circle" width="32" height="32" alt="Avatar">
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $project->creator->name ?? 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $project->members_count ?? 0 }} members</span>
                                </td>
                                <td>
                                    @if($project->budget)
                                        <strong>${{ number_format($project->budget, 0) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" 
                                             style="width: {{ $project->completion_percentage ?? 0 }}%"
                                             role="progressbar"></div>
                                    </div>
                                    <small class="text-muted">{{ $project->completion_percentage ?? 0 }}%</small>
                                </td>
                                <td>
                                    <div>{{ $project->created_at->format('M j, Y') }}</div>
                                    <small class="text-muted">{{ $project->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('projects.show', $project->project_id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('projects.edit', $project->project_id) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                                        <p>No projects found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $projects->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterInputs = document.querySelectorAll('#period, #status, #category');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush