@extends('layout.app')

@section('title', 'System Reports')
@section('page-title', 'System Reports')
@section('page-description', 'Monitor dan analisa aktivitas sistem')

@section('content')
<div class="space-y-6">
    <!-- User Statistics -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">User Statistics</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $userStats['admin_count'] }}</div>
                    <div class="text-sm text-gray-500">Admins</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $userStats['leader_count'] }}</div>
                    <div class="text-sm text-gray-500">Leaders</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $userStats['user_count'] }}</div>
                    <div class="text-sm text-gray-500">Users</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-emerald-600">{{ $userStats['active_users'] }}</div>
                    <div class="text-sm text-gray-500">Active</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $userStats['inactive_users'] }}</div>
                    <div class="text-sm text-gray-500">Inactive</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Statistics -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="100">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Project Statistics</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $projectStats['total_projects'] }}</div>
                    <div class="text-sm text-gray-500">Total Projects</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $projectStats['active_projects'] }}</div>
                    <div class="text-sm text-gray-500">Active</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ $projectStats['completed_projects'] }}</div>
                    <div class="text-sm text-gray-500">Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $projectStats['on_hold_projects'] }}</div>
                    <div class="text-sm text-gray-500">On Hold</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Statistics -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Task Statistics</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $taskStats['total_tasks'] }}</div>
                    <div class="text-sm text-gray-500">Total Tasks</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ $taskStats['todo_tasks'] }}</div>
                    <div class="text-sm text-gray-500">To Do</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $taskStats['in_progress_tasks'] }}</div>
                    <div class="text-sm text-gray-500">In Progress</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $taskStats['completed_tasks'] }}</div>
                    <div class="text-sm text-gray-500">Completed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Projects -->
        <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="300">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Projects</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentActivity['recent_projects'] as $project)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $project->project_name }}</h4>
                            <p class="text-xs text-gray-500">by {{ $project->creator->full_name ?? 'Unknown' }}</p>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $project->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">No recent projects</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="400">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentActivity['recent_users'] as $user)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700">{{ substr($user->full_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $user->full_name }}</h4>
                                <p class="text-xs text-gray-500">{{ ucfirst($user->role) }}</p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">No recent users</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Total Time Logged -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="500">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Total Time Logged</h3>
        </div>
        <div class="p-6 text-center">
            <div class="text-4xl font-bold text-blue-600">{{ number_format($recentActivity['total_time_logged'], 1) }}</div>
            <div class="text-sm text-gray-500 mt-2">Hours logged by all users</div>
        </div>
    </div>
</div>
@endsection