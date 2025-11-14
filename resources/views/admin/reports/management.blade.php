@extends('layout.app')

@section('title', 'Reports & Analytics Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 012-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2z"></path>
                    </svg>
                    Reports & Analytics
                </h1>
                <p class="text-gray-600 mt-2">Comprehensive reporting dashboard untuk sistem manajemen project</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="exportData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Reports
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $overview['total_projects'] ?? 0 }}</h3>
                    <p class="text-gray-600">Total Projects</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $overview['total_users'] ?? 0 }}</h3>
                    <p class="text-gray-600">Active Users</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $overview['total_tasks'] ?? 0 }}</h3>
                    <p class="text-gray-600">Total Tasks</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $timeTracking['total_hours'] ?? 0 }}h</h3>
                    <p class="text-gray-600">Hours Logged</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button onclick="showTab('overview')" id="tab-overview" class="tab-button active py-4 px-6 border-b-2 border-green-500 text-green-600 font-medium text-sm">
                    Overview
                </button>
                <button onclick="showTab('projects')" id="tab-projects" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Project Reports
                </button>
                <button onclick="showTab('users')" id="tab-users" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    User Performance
                </button>
                <button onclick="showTab('time-tracking')" id="tab-time-tracking" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Time Tracking
                </button>
                <button onclick="showTab('analytics')" id="tab-analytics" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Analytics
                </button>
            </nav>
        </div>

        <!-- Overview Tab -->
        <div id="content-overview" class="tab-content p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Project Status Chart -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Status Distribution</h3>
                    <canvas id="projectStatusChart" width="400" height="200"></canvas>
                </div>
                
                <!-- Task Progress Chart -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Progress</h3>
                    <canvas id="taskProgressChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
                <div class="space-y-4">
                    @if(isset($activities) && count($activities) > 0)
                        @foreach($activities as $activity)
                        <div class="flex items-center justify-between py-2 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-gray-700">{{ $activity['description'] ?? 'Activity' }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $activity['time'] ?? 'Just now' }}</span>
                        </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 italic">No recent activities found</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Projects Tab -->
        <div id="content-projects" class="tab-content p-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Active Projects</h4>
                    <p class="text-2xl font-bold text-green-600">{{ $projects['active_projects'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Completed Projects</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ $projects['completed_projects'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">On Hold Projects</h4>
                    <p class="text-2xl font-bold text-orange-600">{{ $projects['on_hold_projects'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Project Progress Chart -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Progress Overview</h3>
                <canvas id="projectProgressChart" width="800" height="400"></canvas>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="content-users" class="tab-content p-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Role Distribution -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Roles</h3>
                    <div class="space-y-3">
                        @if(isset($users['by_role']))
                            @foreach($users['by_role'] as $role => $count)
                            <div class="flex items-center justify-between">
                                <span class="capitalize text-gray-700">{{ $role }}</span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- User Status -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Status</h3>
                    <div class="space-y-3">
                        @if(isset($users['by_status']))
                            @foreach($users['by_status'] as $status => $count)
                            <div class="flex items-center justify-between">
                                <span class="capitalize text-gray-700">{{ $status }}</span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Tracking Tab -->
        <div id="content-time-tracking" class="tab-content p-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Total Hours</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ $timeTracking['total_hours'] ?? 0 }}h</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Sessions</h4>
                    <p class="text-2xl font-bold text-green-600">{{ $timeTracking['total_sessions'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Avg Session</h4>
                    <p class="text-2xl font-bold text-purple-600">{{ $timeTracking['avg_session_length'] ?? 0 }}min</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">Most Productive</h4>
                    <p class="text-lg font-bold text-orange-600">{{ $timeTracking['most_productive_day'] ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Time Tracking Chart -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Time Tracking Trends</h3>
                <canvas id="timeTrackingChart" width="800" height="400"></canvas>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="content-analytics" class="tab-content p-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Performance Metrics -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                    <div class="space-y-4">
                        @if(isset($performance))
                            @foreach($performance as $metric => $value)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $metric)) }}</span>
                                <span class="font-medium">{{ $value }}</span>
                            </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 italic">No performance data available</p>
                        @endif
                    </div>
                </div>

                <!-- Monthly Progress -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Progress</h3>
                    <canvas id="monthlyProgressChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => { content.classList.add('hidden'); });
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => { button.classList.remove('active', 'border-green-500', 'text-green-600'); button.classList.add('border-transparent', 'text-gray-500'); });
    // Show selected tab content
    const el = document.getElementById('content-' + tabName);
    if (el) el.classList.remove('hidden');
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    if (activeButton) { activeButton.classList.add('active', 'border-green-500', 'text-green-600'); activeButton.classList.remove('border-transparent', 'text-gray-500'); }
}

// Chart data from backend
const chartData = @json($charts ?? []);

// Initialize charts after importing Chart.js via Vite
document.addEventListener('DOMContentLoaded', function() {
    import('chart.js/auto').then(({ default: Chart }) => {
        // Project Status Chart
        if (document.getElementById('projectStatusChart')) {
            const ctx1 = document.getElementById('projectStatusChart').getContext('2d');
            new Chart(ctx1, { type: 'doughnut', data: { labels: chartData.taskStatus?.labels || ['To Do', 'In Progress', 'Completed'], datasets: [{ data: chartData.taskStatus?.data || [10,5,15], backgroundColor: ['#f59e0b','#3b82f6','#10b981'] }] }, options: { responsive: true, maintainAspectRatio: false } });
        }

        // Monthly Progress Chart
        if (document.getElementById('monthlyProgressChart')) {
            const ctx2 = document.getElementById('monthlyProgressChart').getContext('2d');
            new Chart(ctx2, { type: 'line', data: { labels: chartData.monthlyProgress?.labels || [], datasets: [{ label: 'Tasks Created', data: chartData.monthlyProgress?.data || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.1 }] }, options: { responsive: true, maintainAspectRatio: false } });
        }
    }).catch(err => console.warn('Chart.js import failed', err));
});

// Export functionality
function exportData() {
    alert('Export functionality will be implemented based on requirements');
}
</script>
@endpush

<style>
.tab-button.active {
    border-color: #10b981 !important;
    color: #10b981 !important;
}

.tab-button:hover:not(.active) {
    color: #374151;
}
</style>
@endsection