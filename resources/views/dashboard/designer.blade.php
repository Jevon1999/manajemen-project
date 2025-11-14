@extends('layout.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Designer Dashboard</h1>
        <p class="text-gray-600">Welcome back, {{ Auth::user()->full_name }}!</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Tasks</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['totalTasks'] }}</p>
                </div>
                <div class="bg-pink-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Designs -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Active Designs</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['activeTasks'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed -->
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

        <!-- Pending Feedback -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending Feedback</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['pendingFeedback'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Design Tasks -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                Active Design Tasks
            </h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($designTasks as $task)
                <div class="border-l-4 border-pink-500 bg-pink-50 p-4 rounded hover:shadow-md transition-shadow">
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
                        <span class="px-2 py-1 rounded {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                    @if($task->comments->count() > 0)
                    <div class="mt-2 text-xs text-purple-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        {{ $task->comments->count() }} comments
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No active design tasks</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                Recent Feedback
            </h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($recentFeedback as $feedback)
                <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-sm text-gray-800">On: {{ $feedback->card_title }}</h3>
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($feedback->created_at)->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $feedback->content }}</p>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No recent feedback</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Asset Requests & Review Notes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Asset Requests -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                Asset Requests
            </h2>
            <div class="space-y-3">
                @forelse($assetRequests as $request)
                <div class="border-l-4 border-orange-500 bg-orange-50 p-4 rounded hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-gray-800 mb-2">{{ $request->title }}</h3>
                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($request->description, 100) }}</p>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Project: {{ $request->board->project->project_name }}</span>
                        <span class="px-2 py-1 rounded bg-orange-100 text-orange-600">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No asset requests</p>
                @endforelse
            </div>
        </div>

        <!-- Review Notes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Review Notes
            </h2>
            <div class="space-y-3">
                @forelse($reviewNotes as $note)
                <div class="border-l-4 border-indigo-500 bg-indigo-50 p-4 rounded hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800">{{ $note->title }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-600">
                            {{ ucfirst(str_replace('_', ' ', $note->status)) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">{{ Str::limit($note->description, 100) }}</p>
                    <div class="text-xs text-gray-500">
                        Project: {{ $note->board->project->project_name }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">No items for review</p>
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

    <!-- Recent Completed Designs -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Recent Completed Designs
        </h2>
        <div class="space-y-3">
            @forelse($recentCompleted as $design)
            <div class="border-l-4 border-green-500 bg-green-50 p-4 rounded">
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-gray-800">{{ $design->title }}</h3>
                    <span class="text-xs text-gray-500">{{ $design->updated_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($design->description, 100) }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">No completed designs yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
