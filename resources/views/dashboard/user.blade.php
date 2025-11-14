@extends('layout.app')

@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')
@section('page-description', 'Track your tasks and progress')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- User Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Tasks -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['totalTasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Tasks -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['activeTasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['completedTasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['overdueTasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- My Active Tasks - Full Width -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">My Active Tasks</h3>
                    <a href="{{ route('tasks.my') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        View all
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
                @forelse($myActiveTasks as $task)
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $task->board->project->project_name ?? 'Unknown Project' }}
                            </p>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($task->status === 'todo') bg-gray-100 text-gray-800
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                @if($task->priority === 'high')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    High Priority
                                </span>
                                @endif
                                @if($task->due_date)
                                <span class="text-xs text-gray-500">
                                    Due {{ $task->due_date->format('M j') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <p>No active tasks</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Completed Tasks & Time Tracking -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Completed -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recently Completed</h3>
            </div>
            <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
                @forelse($recentCompleted as $task)
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $task->board->project->project_name ?? 'Unknown Project' }}
                            </p>
                            <span class="text-xs text-green-600 font-medium">
                                Completed {{ $task->updated_at->diffForHumans() }}
                            </span>
                        </div>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-gray-500">
                    <p class="text-sm">No completed tasks yet</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Weekly Time Summary -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Work Time Tracking</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Today's Time -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Today's Work Time</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $todayFormatted ?? '00:00' }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $todaySeconds = \App\Models\WorkSession::where('user_id', auth()->id())->forDate(now())->sum('duration_seconds');
                            $todayPercent = min(($todaySeconds / 28800) * 100, 100);
                        @endphp
                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $todayPercent }}%"></div>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        Daily limit: 8 hours
                    </div>
                </div>
                
                <!-- This Week's Time -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">This Week's Total</span>
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($timeSpent / 60, 1) }}h</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all" style="width: {{ min(($timeSpent / 60) / 40 * 100, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>Based on 40h work week</span>
                        <span>Daily avg: {{ number_format(($timeSpent / 60) / 7, 1) }}h</span>
                    </div>
                </div>
                
                <!-- Quick Action -->
                <div class="pt-4 border-t border-gray-100">
                    <a href="{{ route('tasks.my') }}" class="block w-full text-center px-4 py-2 bg-blue-50 text-blue-700 font-medium rounded-lg hover:bg-blue-100 transition-colors">
                        Start Working
                    </a>
                </div>
            </div>
        </div>
</div>
@endsection