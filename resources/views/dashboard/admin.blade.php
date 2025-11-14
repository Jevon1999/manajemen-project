@extends('layout.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'System overview and management tools')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- System Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Projects -->
        <div class="ph-metric-card group">
            <div class="absolute -top-4 -left-4 w-20 h-20 rounded-2xl bg-gradient-to-br from-[var(--ph-primary-500)] to-[var(--ph-primary-400)] flex items-center justify-center opacity-90 shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div class="pl-0 sm:pl-16 pt-2">
                <dl>
                    <dt class="text-sm font-semibold truncate" style="color: var(--ph-gray-600)">Total Projects</dt>
                    <dd class="text-2xl font-bold" style="color: var(--ph-gray-900)">{{ $stats['totalProjects'] }}</dd>
                    <dd class="text-sm mt-1" style="color: var(--ph-gray-500)">{{ $stats['activeProjects'] }} active</dd>
                </dl>
            </div>
        </div>

        <!-- Total Users -->
        <div class="ph-metric-card group">
            <div class="absolute -top-4 -left-4 w-20 h-20 rounded-2xl bg-gradient-to-br from-[var(--ph-info)] to-[var(--ph-primary-500)] flex items-center justify-center opacity-90 shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
            <div class="pl-0 sm:pl-16 pt-2">
                <dl>
                    <dt class="text-sm font-semibold truncate" style="color: var(--ph-gray-600)">Total Users</dt>
                    <dd class="text-2xl font-bold" style="color: var(--ph-gray-900)">{{ $stats['totalUsers'] }}</dd>
                    <dd class="text-sm mt-1" style="color: var(--ph-gray-500)">{{ $stats['totalLeaders'] }} leaders</dd>
                </dl>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="ph-metric-card group">
            <div class="absolute -top-4 -left-4 w-20 h-20 rounded-2xl bg-gradient-to-br from-[var(--ph-warning)] to-[var(--ph-primary-500)] flex items-center justify-center opacity-90 shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div class="pl-0 sm:pl-16 pt-2">
                <dl>
                    <dt class="text-sm font-semibold truncate" style="color: var(--ph-gray-600)">Total Tasks</dt>
                    <dd class="text-2xl font-bold" style="color: var(--ph-gray-900)">{{ $stats['totalCards'] }}</dd>
                    <dd class="text-sm mt-1" style="color: var(--ph-gray-500)">{{ $stats['completedCards'] }} completed</dd>
                </dl>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="ph-metric-card group">
            <div class="absolute -top-4 -left-4 w-20 h-20 rounded-2xl bg-gradient-to-br from-[var(--ph-success)] to-[var(--ph-primary-400)] flex items-center justify-center opacity-90 shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="pl-0 sm:pl-16 pt-2">
                <dl>
                    <dt class="text-sm font-semibold truncate" style="color: var(--ph-gray-600)">Completion Rate</dt>
                    @php
                        $completionPercent = $stats['totalCards'] > 0 ? round(($stats['completedCards'] / $stats['totalCards']) * 100, 1) : 0;
                    @endphp
                    <dd class="text-2xl font-bold" style="color: var(--ph-gray-900)">{{ $completionPercent }}%</dd>
                    <dd class="mt-3">
                        <div style="width: 100%; height: 8px; background-color: var(--ph-gray-200); border-radius: var(--ph-radius-sm); overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, var(--ph-success), var(--ph-primary-400)); border-radius: var(--ph-radius-sm); width: {{ $completionPercent }}%; transition: width 0.3s ease-in-out;"></div>
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Projects -->
        <div class="ph-card ph-card-elevated">
            <div class="ph-card-header px-4 sm:px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--ph-gray-900)">Recent Projects</h3>
                        <p class="text-xs" style="color: var(--ph-gray-500)">Latest projects created in the system</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="ph-btn ph-btn-secondary text-sm">
                        View all
                    </a>
                </div>
            </div>
            <div class="ph-card-body px-4 sm:px-6 py-4 sm:py-6 space-y-4">
                @forelse($recentProjects as $project)
                <div class="group relative" style="background-color: var(--ph-surface-secondary); border: 1px solid var(--ph-gray-100); border-radius: var(--ph-radius-lg); padding: var(--ph-space-4); transition: all 0.2s ease-in-out;">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">{{ \Illuminate\Support\Str::title($project->project_name) }}</h4>
                            <p class="text-sm mt-1" style="color: var(--ph-gray-600)">
                                Created by {{ $project->creator->full_name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs mt-1" style="color: var(--ph-gray-500)">
                                {{ $project->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                            {{-- Status badge with new design system --}}
                            @php
                                $status = $project->status;
                            @endphp
                            <span class="ph-badge 
                                @if($status === 'active') ph-badge-success
                                @elseif($status === 'planning') ph-badge-warning
                                @elseif($status === 'completed') ph-badge-info
                                @else ph-badge-error @endif">
                                @if($status === 'active')
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($status === 'planning')
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                                @elseif($status === 'completed')
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                                {{ ucfirst($status) }}
                            </span>

                            {{-- Delete button with new design --}}
                            <form method="POST" action="{{ route('projects.destroy', $project->project_id) }}" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete project" class="ph-btn-icon opacity-0 group-hover:opacity-100 transition-opacity" style="background-color: var(--ph-error-light); color: var(--ph-error);" aria-label="Delete project">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8" style="color: var(--ph-gray-500)">
                    <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--ph-gray-400)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p>No recent projects</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="ph-card ph-card-elevated">
            <div class="ph-card-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--ph-gray-900)">Overdue Tasks</h3>
                        <p class="text-xs" style="color: var(--ph-gray-500)">Tasks past their due date</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="ph-badge ph-badge-error">{{ count($overdueTasks) }} overdue</div>
                        <div class="w-36 sm:w-40 h-11">
                            <canvas id="chart-overdue" class="w-full h-full" width="140" height="44"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $overdueCount = count($overdueTasks);
                $overduePercent = $stats['totalCards'] > 0 ? round(($overdueCount / $stats['totalCards']) * 100, 1) : 0;
            @endphp
            <div style="padding: var(--ph-space-6); border-bottom: 1px solid var(--ph-gray-200);">
                <div class="flex items-center justify-between">
                    <div class="flex-1 mr-4">
                        <div class="text-sm font-medium" style="color: var(--ph-gray-700)">Overdue ratio</div>
                        <div class="mt-2" style="width: 100%; height: 8px; background-color: var(--ph-gray-200); border-radius: var(--ph-radius-sm); overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, var(--ph-error), var(--ph-error-dark)); border-radius: var(--ph-radius-sm); width: {{ $overduePercent }}%; transition: width 0.3s ease-in-out;"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold" style="color: var(--ph-error)">{{ $overduePercent }}%</div>
                        <div class="text-xs" style="color: var(--ph-gray-500)">of all tasks</div>
                    </div>
                </div>
            </div>
            <div class="ph-card-body space-y-4" style="scrollbar-width: thin; scrollbar-color: var(--ph-gray-300) var(--ph-gray-100);">
                @forelse($overdueTasks as $task)
                <div class="group relative" style="background-color: var(--ph-error-light); border: 1px solid var(--ph-error-100); border-radius: var(--ph-radius-lg); padding: var(--ph-space-4); transition: all 0.2s ease-in-out;">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">{{ $task->title }}</h4>
                            <p class="text-sm mt-1" style="color: var(--ph-gray-600)">
                                {{ $task->board->project->project_name ?? 'Unknown Project' }}
                            </p>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="ph-badge ph-badge-error">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                                    </svg>
                                    Overdue {{ $task->due_date->diffForHumans() }}
                                </span>
                                @if($task->assignments->count() > 0)
                                <span class="text-xs" style="color: var(--ph-gray-600)">
                                    • {{ $task->assignments->first()->user->full_name ?? 'Unassigned' }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="viewTaskDetails({{ $task->card_id }})" class="ph-btn-icon" style="background-color: var(--ph-primary-light); color: var(--ph-primary);" title="View task details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8" style="color: var(--ph-gray-500)">
                    <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--ph-success)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No overdue tasks</p>
                    <p class="text-xs mt-1">Great job keeping up with deadlines!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Admin Quick Actions -->
    <div class="ph-card ph-card-elevated">
                <div class="ph-card-header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold" style="color: var(--ph-gray-900)">Admin Tools</h3>
                    <p class="text-xs" style="color: var(--ph-gray-500)">Quick actions and system shortcuts</p>
                </div>
                <div class="w-56 h-12">
                    <canvas id="chart-users" class="w-full h-full" width="220" height="48"></canvas>
                </div>
            </div>
        </div>
        <div class="ph-card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button type="button" onclick="openCreateProjectModal()" class="group flex items-center p-4 rounded-lg transition-all duration-200" style="background: linear-gradient(135deg, var(--ph-primary-50), var(--ph-surface-primary)); border: 1px solid var(--ph-primary-100);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform" style="background-color: var(--ph-primary-500);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">New Project</h4>
                        <p class="text-xs" style="color: var(--ph-gray-600)">Create project</p>
                    </div>
                </button>

                <a href="{{ route('users.index') }}" class="group flex items-center p-4 rounded-lg transition-all duration-200" style="background: linear-gradient(135deg, var(--ph-success-light), var(--ph-surface-primary)); border: 1px solid var(--ph-success-100);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform" style="background-color: var(--ph-success);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">Manage Users</h4>
                        <p class="text-xs" style="color: var(--ph-gray-600)">User administration</p>
                    </div>
                </a>

                <a href="{{ route('leaders.available') }}" class="group flex items-center p-4 rounded-lg transition-all duration-200" style="background: linear-gradient(135deg, var(--ph-warning-light), var(--ph-surface-primary)); border: 1px solid var(--ph-warning-100);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform" style="background-color: var(--ph-warning);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">Leaders</h4>
                        <p class="text-xs" style="color: var(--ph-gray-600)">Manage leaders</p>
                    </div>
                </a>

                <a href="{{ route('reports.index') }}" class="group flex items-center p-4 rounded-lg transition-all duration-200" style="background: linear-gradient(135deg, var(--ph-info-light), var(--ph-surface-primary)); border: 1px solid var(--ph-info-100);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform" style="background-color: var(--ph-info);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h4 class="text-sm font-medium" style="color: var(--ph-gray-900)">Reports</h4>
                        <p class="text-xs" style="color: var(--ph-gray-600)">System analytics</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    </div>
    @php
        // Small client-side datasets as placeholders. Backend can provide richer series via $stats['usersSeries'] or $stats['overdueSeries']
        $usersSeries = $stats['usersSeries'] ?? [3,5,6,8,7,9,10];
        $overdueSeries = $stats['overdueSeries'] ?? [2,3,1,4,3,2,5];
    @endphp

    <script>
        window.dashboardData = {
            usersSeries: {!! json_encode($usersSeries) !!},
            overdueSeries: {!! json_encode($overdueSeries) !!},
            completionPercent: {{ $completionPercent ?? 0 }},
            overduePercent: {{ $overduePercent ?? 0 }}
        };
    </script>

    <!-- Chart.js is loaded via the Vite bundle (dynamically imported by resources/js/site.js) -->

    @include('admin.partials.create-project-modal')
    @include('admin.partials.task-detail-modal')
</div>
@endsection