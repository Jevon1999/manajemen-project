@extends('layout.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Developer Dashboard</h1>
        <p class="text-gray-600">Welcome back, {{ Auth::user()->full_name }}!</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Tasks</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['totalTasks'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Active Tasks</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['activeTasks'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Completed</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['completedTasks'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Overdue</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['overdueTasks'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Tracking Summary -->
    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8 text-white">
        <h2 class="text-xl font-bold mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Time Tracking
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <p class="text-sm opacity-90">Today</p>
                <p class="text-2xl font-bold">{{ floor($timeStats['today'] / 60) }}h {{ $timeStats['today'] % 60 }}m</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <p class="text-sm opacity-90">This Week</p>
                <p class="text-2xl font-bold">{{ floor($timeStats['thisWeek'] / 60) }}h {{ $timeStats['thisWeek'] % 60 }}m</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <p class="text-sm opacity-90">This Month</p>
                <p class="text-2xl font-bold">{{ floor($timeStats['thisMonth'] / 60) }}h {{ $timeStats['thisMonth'] % 60 }}m</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Active Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Active Tasks
            </h2>
            <div class="space-y-3">
                @forelse($activeTasks as $task)
                <div class="border-l-4 border-blue-500 bg-gray-50 p-4 rounded hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800">{{ $task->title }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full 
                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-600' : 
                               ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                            {{ ucfirst($task->priority ?? 'medium') }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($task->description, 100) }}</p>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No deadline' }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ floor($task->time_spent / 60) }}h {{ $task->time_spent % 60 }}m
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No active tasks</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Upcoming Deadlines (Next 7 Days)
            </h2>
            <div class="space-y-3">
                @forelse($upcomingDeadlines as $task)
                <div class="border-l-4 border-orange-500 bg-orange-50 p-4 rounded hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800">{{ $task->title }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-600">
                            {{ $task->due_date ? $task->due_date->diffForHumans() : 'No deadline' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($task->description, 100) }}</p>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Project: {{ $task->board->project->project_name }}</span>
                        <span class="px-2 py-1 rounded {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No upcoming deadlines</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- My Projects -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            My Projects
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($myProjects as $project)
            <a href="{{ route('admin.projects.show', $project->project_id) }}" class="block border rounded-lg p-4 hover:shadow-lg transition-shadow">
                <h3 class="font-semibold text-gray-800 mb-2">{{ $project->project_name }}</h3>
                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($project->description, 80) }}</p>
                <div class="flex justify-between items-center">
                    <span class="text-xs px-2 py-1 rounded-full 
                        {{ $project->status === 'active' ? 'bg-green-100 text-green-600' : 
                           ($project->status === 'planning' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($project->status) }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $project->members->count() }} members</span>
                </div>
            </a>
            @empty
            <p class="text-gray-500 text-center py-8 col-span-3">No projects yet</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Completed Tasks -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Recent Completed Tasks
        </h2>
        <div class="space-y-3">
            @forelse($recentCompleted as $task)
            <div class="border-l-4 border-green-500 bg-green-50 p-4 rounded">
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-gray-800">{{ $task->title }}</h3>
                    <span class="text-xs text-gray-500">{{ $task->updated_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($task->description, 100) }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">No completed tasks yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
