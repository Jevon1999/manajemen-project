@extends('layout.app')

@section('title', 'Tasks - ' . $project->project_name)

@section('page-title', 'Project Tasks')
@section('page-description', $project->project_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <span class="text-gray-800">
                    {{ $project->project_name }}
                </span>
                <span class="text-gray-400">/</span> Tasks
            </h1>
            <p class="text-gray-600">Manage project tasks and track progress</p>
        </div>
        @if($canManage)
        <div>
            <a href="{{ route('admin.projects.tasks.create', $project->project_id) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Task
            </a>
        </div>
        @endif
    </div>

    <!-- Task Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-blue-600 text-sm font-medium">Total</div>
            <div class="text-2xl font-bold text-blue-800">{{ $statistics['total'] ?? 0 }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="text-gray-600 text-sm font-medium">To Do</div>
            <div class="text-2xl font-bold text-gray-800">{{ $statistics['todo'] ?? 0 }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="text-yellow-600 text-sm font-medium">In Progress</div>
            <div class="text-2xl font-bold text-yellow-800">{{ $statistics['in_progress'] ?? 0 }}</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-purple-600 text-sm font-medium">Review</div>
            <div class="text-2xl font-bold text-purple-800">{{ $statistics['review'] ?? 0 }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-green-600 text-sm font-medium">Done</div>
            <div class="text-2xl font-bold text-green-800">{{ $statistics['done'] ?? 0 }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <div class="text-red-600 text-sm font-medium">Overdue</div>
            <div class="text-2xl font-bold text-red-800">{{ $statistics['overdue'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'status' => '']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('status') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Tasks
            </a>
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'status' => 'todo']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'todo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                To Do ({{ $statistics['todo'] ?? 0 }})
            </a>
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'status' => 'in_progress']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'in_progress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                In Progress ({{ $statistics['in_progress'] ?? 0 }})
            </a>
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'status' => 'review']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'review' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Review ({{ $statistics['review'] ?? 0 }})
            </a>
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'status' => 'done']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ request('status') === 'done' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Done ({{ $statistics['done'] ?? 0 }})
            </a>
            <a href="{{ route('admin.projects.tasks.index', [$project->project_id, 'overdue' => '1']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ request('overdue') ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Overdue ({{ $statistics['overdue'] ?? 0 }})
            </a>
        </nav>
    </div>

    <!-- Tasks List -->
    <div class="space-y-4">
        @forelse($tasks as $task)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow {{ $task->status === 'done' ? 'bg-green-50 border-green-200' : '' }}">
            <div class="p-6 {{ $task->status === 'done' ? 'opacity-90' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Task Header -->
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">
                                @if(auth()->user()->role !== 'user' && auth()->user()->role !== 'developer' && auth()->user()->role !== 'designer')
                                    <a href="{{ route('admin.projects.tasks.show', [$project->project_id, $task->task_id]) }}" class="hover:text-blue-600">
                                        {{ $task->title }}
                                    </a>
                                @else
                                    {{ $task->title }}
                                @endif
                            </h3>
                            
                            <!-- Status Badge -->
                            @if($task->status === 'todo')
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">To Do</span>
                            @elseif($task->status === 'in_progress')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">In Progress</span>
                            @elseif($task->status === 'review')
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Review</span>
                            @elseif($task->status === 'done')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    ✅ Done
                                    @if($task->completed_at)
                                        <span class="text-green-600">• {{ $task->completed_at->format('M j') }}</span>
                                    @endif
                                </span>
                            @endif

                            <!-- Priority -->
                            @if($task->priority === 'high')
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">🔴 High</span>
                            @elseif($task->priority === 'medium')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">🟡 Medium</span>
                            @elseif($task->priority === 'low')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">🟢 Low</span>
                            @endif

                            <!-- Extension Request Badge for Leader -->
                            @php
                                $isLeader = $task->project->leader_id === Auth::id();
                                $hasPendingExtension = $task->extensionRequests()
                                    ->where('status', 'pending')
                                    ->exists();
                            @endphp
                            @if($isLeader && $hasPendingExtension)
                                <span class="px-3 py-1 text-xs font-bold bg-gradient-to-r from-orange-400 to-red-400 text-white rounded-full animate-pulse shadow-md">
                                    ⏰ Extension Request
                                </span>
                            @endif
                        </div>

                        <!-- Task Description -->
                        @if($task->description)
                        <p class="text-gray-600 mb-3 line-clamp-2">{{ Str::limit($task->description, 150) }}</p>
                        @endif

                        <!-- Task Meta -->
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span>{{ $task->project->project_name ?? 'Unknown Project' }}</span>
                            </div>
                            @if($task->deadline)
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="{{ $task->deadline < now() && $task->status !== 'done' ? 'text-red-500 font-medium' : '' }}">
                                    Due {{ $task->deadline->format('M j, Y') }}
                                </span>
                            </div>
                            @endif
                            @if($task->assignedUser)
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>{{ $task->assignedUser->full_name ?? 'Unassigned' }}</span>
                            </div>
                            @else
                            <div class="flex items-center space-x-1 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>Unassigned</span>
                            </div>
                            @endif
                            @if($task->subtasks->count() > 0)
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                <span>{{ $task->subtasks->where('is_completed', true)->count() }}/{{ $task->subtasks->count() }} subtasks</span>
                            </div>
                            @endif
                            @if($task->status === 'done' && $task->completed_at)
                            <div class="flex items-center space-x-1 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Completed {{ $task->completed_at->format('M j, Y g:i A') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="ml-4 flex items-center space-x-2">
                        @if($task->status !== 'done' && $task->assigned_to === Auth::id())
                        <form method="POST" action="{{ route('tasks.update-status', $task->task_id) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($task->status === 'todo')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                                Start Working
                            </button>
                            @elseif($task->status === 'in_progress')
                            <input type="hidden" name="status" value="review">
                            <button type="submit" 
                                    onclick="return confirm('Yakin ingin menyelesaikan task ini dan mengirim ke review?')"
                                    class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition-colors">
                                @if(in_array(auth()->user()->role, ['developer','designer']))
                                    Selesaikan Task
                                @else
                                    Send for Review
                                @endif
                            </button>
                            @elseif($task->status === 'review')
                            <input type="hidden" name="status" value="done">
                            <button type="submit" class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors">
                                Mark Complete
                            </button>
                            @endif
                        </form>
                        @endif
                        
                        {{-- Leader Actions for Review Tasks --}}
                        @if($task->status === 'review' && in_array(auth()->user()->role, ['admin', 'leader']))
                            {{-- Done Button --}}
                            <form method="POST" action="{{ route('tasks.transitions.approve', $task->task_id) }}" 
                                  class="inline" onsubmit="return handleApproveSubmit(event, this)">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors">
                                    ✓ Done
                                </button>
                            </form>
                            
                            {{-- Reject Button --}}
                            <button onclick="showRejectModal({{ $task->task_id }})" 
                                    class="px-3 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition-colors">
                                ✗ Reject
                            </button>
                        @endif
                        
                        {{-- View Details Button for Admin/Leader --}}
                        @if(in_array(auth()->user()->role, ['admin', 'leader']))
                            <button onclick="toggleTaskDetails({{ $task->task_id }})" 
                                    class="px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                                    id="toggle-btn-{{ $task->task_id }}">
                                View Details
                            </button>
                        @endif

                        {{-- Extension Request Quick Actions for Leader --}}
                        @php
                            $isLeader = $task->project->leader_id === Auth::id();
                            $pendingExtension = $task->extensionRequests()
                                ->where('status', 'pending')
                                ->first();
                        @endphp
                        @if($isLeader && $pendingExtension)
                            <a href="{{ route('admin.projects.tasks.show', [$project->project_id, $task->task_id]) }}" 
                               class="px-3 py-1 text-xs font-bold bg-orange-500 text-white rounded-full hover:bg-orange-600 transition-colors shadow-sm">
                                🔔 Review Extension Request
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Task Details (Hidden by default) -->
                @if(in_array(auth()->user()->role, ['admin', 'leader']))
                <div id="task-details-{{ $task->task_id }}" class="hidden border-t border-gray-200 pt-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Subtasks</h4>
                        
                        <div id="subtasks-container-{{ $task->task_id }}">
                            <div class="text-center py-4">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                                <p class="text-sm text-gray-500 mt-2">Loading subtasks...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            @if(request('status') === 'done')
                <svg class="w-16 h-16 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No completed tasks yet</h3>
                <p class="text-gray-500">
                    Tasks that are approved by leaders will appear here.
                    <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">View all tasks</a>
                </p>
            @elseif(request('status') === 'review')
                <svg class="w-16 h-16 text-purple-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks under review</h3>
                <p class="text-gray-500">
                    Tasks submitted for review by team members will appear here.
                    <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">View all tasks</a>
                </p>
            @elseif(request('status') === 'in_progress')
                <svg class="w-16 h-16 text-yellow-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks in progress</h3>
                <p class="text-gray-500">
                    Tasks currently being worked on will appear here.
                    <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">View all tasks</a>
                </p>
            @elseif(request('status') === 'todo')
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No pending tasks</h3>
                <p class="text-gray-500">
                    New tasks waiting to be started will appear here.
                    <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">View all tasks</a>
                </p>
            @else
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                <p class="text-gray-500">
                    @if(request('status') || request('priority') || request('assigned_to'))
                        No tasks match the current filter. <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">View all tasks</a>
                    @else
                        No tasks in this project yet.
                        @if($canManage)
                            <a href="{{ route('admin.projects.tasks.create', $project->project_id) }}" class="text-blue-600 hover:text-blue-800">Create first task</a>
                        @endif
                    @endif
                </p>
            @endif
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
    <div class="mt-6">
        {{ $tasks->appends(request()->query())->links() }}
    </div>
    @endif

    <!-- Project Discussion Section -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Project Discussion
                </h3>
                <p class="text-sm text-gray-500 mt-1">Diskusi tim tentang project {{ $project->project_name }}</p>
            </div>
            <span id="project-comments-count" class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">0</span>
        </div>

        <!-- Add Comment Form -->
        <div class="mb-6">
            <form onsubmit="addProjectComment(event)" class="space-y-3">
                <div>
                    <textarea id="project-comment-input" rows="3" placeholder="Tulis komentar atau diskusi tentang project..." 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" 
                              required></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">💡 Bagikan update, diskusi, atau pertanyaan tentang project</p>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 font-medium shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Kirim</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Comments List -->
        <div id="project-comments-list" class="space-y-4">
            <div class="text-center py-8">
                <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto"></div>
                <p class="text-gray-500 mt-3">Memuat diskusi...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div id="project-comments-empty" class="hidden text-center py-8">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p class="text-gray-500 font-medium">Belum ada diskusi</p>
            <p class="text-sm text-gray-400 mt-1">Jadilah yang pertama memulai diskusi project ini</p>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Reject Task</h3>
                <button onclick="hideRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="rejectForm" method="POST" action="" onsubmit="return submitRejectForm(event)">
                @csrf
                <div class="mb-4">
                    <label for="rejectReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Reject (Opsional)
                    </label>
                    <textarea id="rejectReason" name="reason" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Berikan alasan mengapa task di-reject..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideRejectModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Reject Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Reject Modal Functions
function showRejectModal(taskId) {
    console.log('showRejectModal called for task:', taskId);
    
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    
    if (!modal) {
        console.error('Modal element not found');
        alert('Error: Modal not found');
        return;
    }
    
    if (!form) {
        console.error('Form element not found');
        alert('Error: Form not found');
        return;
    }
    
    // Set form action URL  
    form.action = `/tasks/${taskId}/transitions/reject`;
    console.log('Form action set to:', form.action);
    
    // Show modal by removing hidden class
    modal.classList.remove('hidden');
    console.log('Modal classes after showing:', modal.className);
    
    // Focus on textarea
    const textarea = document.getElementById('rejectReason');
    if (textarea) {
        setTimeout(() => textarea.focus(), 100);
    }
}

function hideRejectModal() {
    console.log('hideRejectModal called');
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    
    if (modal) {
        modal.classList.add('hidden');
        console.log('Modal hidden');
    }
    
    if (form) {
        form.reset();
        console.log('Form reset');
    }
}

// Submit reject form via AJAX
function submitRejectForm(event) {
    console.log('submitRejectForm called');
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const actionUrl = form.action;
    
    console.log('Submitting to URL:', actionUrl);
    
    // Disable submit button to prevent double submission
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Memproses...';
    
    fetch(actionUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            hideRejectModal();
            showNotification(data.message || 'Task berhasil di-reject!', 'success');
            // Reload page to update task status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Gagal reject task');
        }
    })
    .catch(error => {
        console.error('Error rejecting task:', error);
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
    
    return false;
}

// Handle approve form submission
function handleApproveSubmit(event, form) {
    event.preventDefault();
    
    if (!confirm('Yakin ingin menyetujui dan menandai task ini sebagai selesai?')) {
        return false;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Memproses...';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        console.log('Approve response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Approve response data:', data);
        if (data.success) {
            showNotification(data.message || 'Task berhasil di-approve!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Gagal approve task');
        }
    })
    .catch(error => {
        console.error('Error approving task:', error);
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
    
    return false;
}

// Close modal when clicking outside
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Role filter handler
document.getElementById('roleFilter')?.addEventListener('change', function() {
    const role = this.value;
    const url = new URL(window.location);
    if (role) {
        url.searchParams.set('role_filter', role);
    } else {
        url.searchParams.delete('role_filter');
    }
    window.location.href = url.toString();
});

// Toggle task details and load subtasks
function toggleTaskDetails(taskId) {
    const detailsDiv = document.getElementById(`task-details-${taskId}`);
    const toggleBtn = document.getElementById(`toggle-btn-${taskId}`);
    const subtasksContainer = document.getElementById(`subtasks-container-${taskId}`);
    
    if (detailsDiv.classList.contains('hidden')) {
        // Show details and load subtasks
        detailsDiv.classList.remove('hidden');
        toggleBtn.textContent = 'Hide Details';
        loadSubtasks(taskId);
    } else {
        // Hide details
        detailsDiv.classList.add('hidden');
        toggleBtn.textContent = 'View Details';
    }
}

// Load subtasks via AJAX
function loadSubtasks(taskId) {
    const container = document.getElementById(`subtasks-container-${taskId}`);
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-sm text-gray-500 mt-2">Loading subtasks...</p>
        </div>
    `;
    
    fetch(`/tasks/${taskId}/subtasks`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.subtasks && data.subtasks.length > 0) {
                let html = '<div class="space-y-2">';
                data.subtasks.forEach(subtask => {
                    const isCompleted = subtask.is_completed;
                    const priority = subtask.priority || 'medium';
                    const priorityColor = priority === 'high' ? 'text-red-600' : 
                                         priority === 'low' ? 'text-green-600' : 'text-yellow-600';
                    const priorityBg = priority === 'high' ? 'bg-red-50 border-red-200' : 
                                      priority === 'low' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200';
                    
                    html += `
                        <div class="flex items-center justify-between p-3 bg-white rounded border hover:shadow-sm transition-shadow ${isCompleted ? 'opacity-75' : ''}">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="flex items-center">
                                    ${isCompleted ? 
                                        '<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                                        '<svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle></svg>'
                                    }
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium ${isCompleted ? 'line-through text-gray-500' : 'text-gray-900'}">${subtask.title}</span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full ${priorityBg} ${priorityColor}">
                                            ${priority.charAt(0).toUpperCase() + priority.slice(1)}
                                        </span>
                                    </div>
                                    ${subtask.description ? `<p class="text-xs text-gray-500 mt-1 ${isCompleted ? 'line-through' : ''}">${subtask.description}</p>` : ''}
                                    <div class="flex items-center space-x-3 mt-2 text-xs text-gray-400">
                                        <span>Created: ${subtask.created_at}</span>
                                        ${isCompleted ? `<span>Completed: ${subtask.updated_at}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                ${isCompleted ? 
                                    '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">✅ Completed</span>' :
                                    '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">⏳ Pending</span>'
                                }
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                // Add summary
                const completedCount = data.subtasks.filter(s => s.is_completed).length;
                const totalCount = data.subtasks.length;
                const progressPercentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
                
                html = `
                    <div class="flex items-center justify-between mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="text-sm font-medium text-blue-900">
                            📊 Subtask Progress: ${completedCount}/${totalCount} completed (${progressPercentage}%)
                        </div>
                        <div class="w-32 h-3 bg-blue-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-500" 
                                 style="width: ${progressPercentage}%"></div>
                        </div>
                    </div>
                ` + html;
                
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-sm text-gray-500 mb-2">📝 No subtasks found for this task</p>
                        <p class="text-xs text-gray-400">Subtasks help break down complex tasks into manageable steps</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading subtasks:', error);
            container.innerHTML = `
                <div class="text-center py-6 text-red-600">
                    <svg class="w-12 h-12 text-red-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.194-.833-2.964 0L4.85 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-sm font-medium mb-2">⚠️ Error loading subtasks</p>
                    <p class="text-xs text-gray-500 mb-3">${error.message}</p>
                    <button onclick="loadSubtasks(${taskId})" class="px-4 py-2 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors">
                        🔄 Retry
                    </button>
                </div>
            `;
        });
}

// Show success/error messages from Laravel session
@if(session('success'))
    // Simple notification function
    const showNotification = (message, type) => {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    };
    
    showNotification('{{ session('success') }}', 'success');
@endif

@if(session('error'))
    showNotification('{{ session('error') }}', 'error');
@endif

// ============= PROJECT COMMENTS FUNCTIONALITY =============
const projectId = {{ $project->project_id }};

// Load project comments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProjectComments();
});

// Load all project comments
async function loadProjectComments() {
    try {
        const response = await fetch(`/projects/${projectId}/comments`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            displayProjectComments(data.comments);
            updateProjectCommentsCount(data.total);
        } else {
            console.error('Failed to load comments');
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        document.getElementById('project-comments-list').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-500">Gagal memuat diskusi</p>
            </div>
        `;
    }
}

// Display project comments
function displayProjectComments(comments) {
    const commentsList = document.getElementById('project-comments-list');
    const emptyState = document.getElementById('project-comments-empty');

    if (comments.length === 0) {
        commentsList.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }

    commentsList.classList.remove('hidden');
    emptyState.classList.add('hidden');

    commentsList.innerHTML = comments.map(comment => {
        // Role badge colors
        const roleBadge = comment.user.role === 'admin' ? 
            '<span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded font-medium">Admin</span>' :
            comment.user.role === 'leader' ?
            '<span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded font-medium">Leader</span>' :
            '<span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded font-medium">Member</span>';

        return `
        <div class="flex space-x-3 group hover:bg-gray-50 p-3 rounded-lg transition-colors" data-comment-id="${comment.comment_id}">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-md">
                    <span class="text-sm font-bold text-white">${comment.user.initials}</span>
                </div>
            </div>
            
            <!-- Comment Content -->
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-semibold text-gray-900">${comment.user.name}</span>
                            ${roleBadge}
                        </div>
                        ${comment.is_owner ? `
                            <button onclick="deleteProjectComment(${comment.comment_id})" 
                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" 
                                    title="Hapus komentar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                    <p class="text-gray-700 text-sm whitespace-pre-wrap break-words leading-relaxed">${escapeHtml(comment.comment)}</p>
                </div>
                <div class="flex items-center space-x-2 mt-1 px-3">
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs text-gray-500">${comment.created_at_human}</span>
                </div>
            </div>
        </div>
    `}).join('');
}

// Add new project comment
async function addProjectComment(event) {
    event.preventDefault();

    const commentInput = document.getElementById('project-comment-input');
    const comment = commentInput.value.trim();

    if (!comment) {
        showNotification('Komentar tidak boleh kosong', 'error');
        return;
    }

    try {
        const response = await fetch(`/projects/${projectId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ comment })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Komentar berhasil ditambahkan', 'success');
            commentInput.value = '';
            loadProjectComments();
        } else {
            showNotification(data.error || 'Gagal menambahkan komentar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan komentar', 'error');
    }
}

// Delete project comment
async function deleteProjectComment(commentId) {
    if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
        return;
    }

    try {
        const response = await fetch(`/projects/${projectId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Komentar berhasil dihapus', 'success');
            loadProjectComments();
        } else {
            showNotification(data.error || 'Gagal menghapus komentar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus komentar', 'error');
    }
}

// Update project comments count
function updateProjectCommentsCount(count) {
    const badge = document.getElementById('project-comments-count');
    if (badge) {
        badge.textContent = count;
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection