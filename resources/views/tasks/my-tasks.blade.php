@extends('layout.app')

@section('title', 'My Tasks')
@section('page-title', 'My Tasks')
@section('page-description', 'Manage your assigned tasks')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header with Work Timer -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Tasks</h1>
            <p class="text-gray-600">Tasks assigned to you across all projects</p>
        </div>
        
        <!-- Work Timer Controls -->
        <div class="flex items-center space-x-4">
            <!-- Today's Total Time -->
            <div class="bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                <div class="text-xs text-gray-500">Today's Total</div>
                <div class="text-lg font-bold text-gray-800" id="today-total-display">00:00:00</div>
                <div class="text-xs text-gray-500">
                    Remaining: <span id="remaining-time-display" class="font-medium text-green-600">08:00</span>
                </div>
            </div>
            
            <!-- Timer Display (shown when timer is active) -->
            <div id="timer-container" class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-200" style="display: none;">
                <div class="text-xs text-blue-600">Current Session</div>
                <div class="text-2xl font-bold text-blue-800 font-mono" id="timer-display">00:00:00</div>
            </div>
            
            <!-- Start Work Button -->
            <button id="start-work-btn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Start Work
            </button>
            
            <!-- Stop Work Button (hidden by default) -->
            <button id="stop-work-btn" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
                Stop Work
            </button>
        </div>
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

    <!-- Tasks List -->
    <div class="space-y-4">
        @forelse($tasks as $task)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow" data-task-id="{{ $task->task_id }}">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Task Info -->
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ $task->title }}
                            </h3>
                            
                            <!-- Status Badge -->
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($task->status === 'todo') bg-gray-100 text-gray-700
                                @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800  
                                @elseif($task->status === 'review') bg-purple-100 text-purple-800
                                @elseif($task->status === 'done') bg-green-100 text-green-800
                                @elseif($task->status === 'overdue') bg-red-100 text-red-800
                                @endif
                            ">
                                @if($task->status === 'todo') To Do
                                @elseif($task->status === 'in_progress') In Progress
                                @elseif($task->status === 'review') Review
                                @elseif($task->status === 'done') Done
                                @elseif($task->status === 'overdue') ⚠️ Overdue
                                @endif
                            </span>
                            
                            <!-- Priority Badge -->
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($task->priority === 'low') bg-blue-100 text-blue-800
                                @elseif($task->priority === 'medium') bg-orange-100 text-orange-800
                                @elseif($task->priority === 'high') bg-red-100 text-red-800
                                @endif
                            ">
                                @if($task->priority === 'low') Low Priority
                                @elseif($task->priority === 'medium') Medium Priority  
                                @elseif($task->priority === 'high') High Priority
                                @endif
                            </span>
                        </div>
                        
                        <!-- Project & Description -->
                        <div class="text-gray-600 mb-2">
                            <span class="font-medium">Project:</span>
                            <span class="text-gray-700">
                                {{ $task->project->project_name ?? 'Unknown Project' }}
                            </span>
                        </div>
                        
                        @if($task->description)
                        <p class="text-gray-600 text-sm mb-2">{{ Str::limit($task->description, 100) }}</p>
                        @endif
                        
                        <!-- Deadline & Extension Request -->
                        @if($task->deadline)
                        <div class="text-sm text-gray-500 mb-2">
                            <span class="font-medium">Deadline:</span>
                            <span class="{{ $task->deadline->isPast() && $task->status !== 'done' ? 'text-red-600 font-semibold' : '' }}">
                                {{ $task->deadline->format('M j, Y') }}
                                @if($task->deadline->isPast() && $task->status !== 'done')
                                    <span class="text-red-500">(Overdue)</span>
                                @endif
                            </span>
                        </div>
                        
                        @php
                            $isOverdue = $task->deadline && $task->deadline->isPast() && $task->status !== 'done';
                            $isAssigned = $task->assigned_to === Auth::id();
                            $hasPendingRequest = $task->hasPendingExtensionRequest();
                        @endphp
                        
                        @if($isOverdue && $isAssigned)
                            <!-- Blocked Badge -->
                            @if($task->is_blocked)
                                <div class="mb-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-300">
                                        🔒 Task Blocked
                                    </span>
                                    @if($task->block_reason)
                                        <p class="text-xs text-gray-600 mt-1">{{ $task->block_reason }}</p>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Extension Request Button or Status -->
                            @if($task->is_blocked && !$hasPendingRequest)
                                <button 
                                    onclick="openExtensionModalMyTasks({{ $task->task_id }}, '{{ $task->title }}', '{{ $task->deadline->format('Y-m-d') }}')"
                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-full shadow transition-all"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Ajukan Perpanjangan
                                </button>
                            @elseif($hasPendingRequest)
                                <div class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-yellow-50 border border-yellow-200 rounded-full text-yellow-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Menunggu Persetujuan Leader
                                </div>
                            @endif
                        @endif
                        @endif

                        <!-- Subtasks Todolist -->
                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-700">Task Checklist</h4>
                                <button onclick="showAddSubtaskForm({{ $task->task_id }})" 
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    + Add Item
                                </button>
                            </div>

                            <!-- Add Subtask Form (Initially Hidden) -->
                            <form id="add-subtask-form-{{ $task->task_id }}" class="hidden mb-3" 
                                  onsubmit="addSubtask(event, {{ $task->task_id }})">
                                <div class="flex space-x-2">
                                    <input type="text" 
                                           name="title" 
                                           placeholder="Enter checklist item..."
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    <button type="submit" 
                                            class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                        Add
                                    </button>
                                    <button type="button" 
                                            onclick="hideAddSubtaskForm({{ $task->task_id }})"
                                            class="px-3 py-1 bg-gray-300 text-gray-700 text-xs rounded hover:bg-gray-400">
                                        Cancel
                                    </button>
                                </div>
                            </form>

                            <!-- Subtasks List -->
                            <div id="subtasks-list-{{ $task->task_id }}" class="space-y-1">
                                @forelse($task->subtasks ?? [] as $subtask)
                                <div class="flex items-center space-x-2 text-sm group" data-subtask-id="{{ $subtask->subtask_id }}">
                                    <input type="checkbox" 
                                           {{ $subtask->is_completed ? 'checked disabled' : '' }}
                                           onchange="toggleSubtask({{ $subtask->subtask_id }})"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 {{ $subtask->is_completed ? 'cursor-not-allowed' : '' }}"
                                           title="{{ $subtask->is_completed ? 'Subtask yang sudah selesai tidak dapat diubah' : '' }}">
                                    
                                    <!-- Task Title (view mode) -->
                                    <span class="flex-1 {{ $subtask->is_completed ? 'line-through text-gray-400' : 'text-gray-700' }}"
                                          id="subtask-title-{{ $subtask->subtask_id }}">
                                        {{ $subtask->title }}
                                        @if($subtask->is_completed)
                                            <span class="ml-2 text-xs text-green-600">✓ Selesai</span>
                                        @endif
                                    </span>
                                    
                                    <!-- Edit Form (hidden by default) -->
                                    <form id="edit-form-{{ $subtask->subtask_id }}" class="hidden flex-1 space-x-1" 
                                          onsubmit="saveSubtaskEdit(event, {{ $subtask->subtask_id }})">>
                                        <input type="text" 
                                               name="title" 
                                               value="{{ $subtask->title }}"
                                               class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                        <button type="submit" 
                                                class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                            ✓
                                        </button>
                                        <button type="button" 
                                                onclick="cancelSubtaskEdit({{ $subtask->subtask_id }})"
                                                class="px-2 py-1 bg-gray-400 text-white text-xs rounded hover:bg-gray-500">
                                            ✕
                                        </button>
                                    </form>
                                    
                                    <!-- Action Icons -->
                                    @if(!$subtask->is_completed)
                                    <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="editSubtask({{ $subtask->subtask_id }})" 
                                                id="edit-btn-{{ $subtask->subtask_id }}"
                                                class="p-1 text-blue-500 hover:text-blue-700 text-xs rounded hover:bg-blue-50"
                                                title="Edit item">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteSubtask({{ $subtask->subtask_id }})" 
                                                class="p-1 text-red-500 hover:text-red-700 text-xs rounded hover:bg-red-50"
                                                title="Delete item">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <p class="text-xs text-gray-400 italic">No checklist items yet</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="ml-4 flex items-center space-x-2">
                        {{-- Selesaikan Task Action for Todo and In Progress --}}
                        @if(in_array($task->status, ['todo', 'in_progress']) && $task->assigned_to === Auth::id())
                        <form method="POST" action="{{ route('tasks.update-status-simple', $task) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="review">
                            <button type="submit" 
                                    onclick="return confirm('Yakin ingin menyelesaikan task ini dan mengirim ke review?')"
                                    class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors">
                                ✅ Selesaikan Task
                            </button>
                        </form>
                        @else
                        {{-- Debug: Tampilkan info kenapa button tidak muncul --}}
                        <span class="text-xs text-gray-400" title="Status: {{ $task->status }}, Assigned: {{ $task->assigned_to }}, Auth: {{ Auth::id() }}">
                            @if($task->assigned_to !== Auth::id())
                                Not your task
                            @elseif(!in_array($task->status, ['todo', 'in_progress']))
                                Status: {{ $task->status }}
                            @endif
                        </span>
                        @endif
                        
                        {{-- Review Actions for Leaders --}}
                        @if($task->status === 'review')
                        <span class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                            ⏳ Menunggu Review
                        </span>
                        @endif
                        
                        {{-- Done Status --}}
                        @if($task->status === 'done')
                        <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            ✅ Selesai
                        </span>
                        @endif
                        
                        @if(auth()->user()->role !== 'user' && auth()->user()->role !== 'developer' && auth()->user()->role !== 'designer')
                        <a href="{{ route('tasks.show', $task->task_id) }}" class="px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                            View Details
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks assigned</h3>
            <p class="text-gray-500">You don't have any tasks assigned to you yet.</p>
        </div>
        @endforelse
    </div>

    <!-- Project Discussions Section -->
    @php
        // Get user's projects for discussion
        $userProjects = \App\Models\Project::whereHas('members', function($query) {
            $query->where('user_id', Auth::id());
        })->with(['leader', 'members'])->get();
    @endphp
    
    @if($userProjects->count() > 0)
    <div class="mt-12 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Project Discussions</h2>
                        <p class="text-sm text-gray-600">Collaborate with your team on project-related topics</p>
                    </div>
                </div>
                <div class="text-sm text-gray-500">
                    💡 Stay connected with your project team
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Project Tabs -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-6 overflow-x-auto">
                    @foreach($userProjects as $index => $project)
                    <button class="project-discussion-tab {{ $index === 0 ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center space-x-2"
                            data-project-id="{{ $project->project_id }}"
                            data-project-name="{{ $project->project_name }}">
                        <span>{{ Str::limit($project->project_name, 25) }}</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                            {{ $project->members->count() }} members
                        </span>
                    </button>
                    @endforeach
                </nav>
            </div>

            <!-- Discussion Content -->
            <div class="project-discussion-content">
                <!-- Comments Container -->
                <div id="project-comments-container" class="mb-6">
                    <div class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-gray-500">Loading discussions...</span>
                    </div>
                </div>

                <!-- Comment Form -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <form id="project-comment-form" class="space-y-4">
                        <div>
                            <label for="project-comment" class="block text-sm font-medium text-gray-700 mb-2">
                                Join the project discussion
                            </label>
                            <textarea id="project-comment" 
                                      name="comment" 
                                      rows="3" 
                                      class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      placeholder="Share your thoughts, ask questions, or provide project updates..."
                                      maxlength="1000"></textarea>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500">
                                    <span id="comment-char-count">0</span>/1000 characters
                                </span>
                                <span class="text-xs text-gray-500">
                                    💬 Visible to all project members
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-2"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H9v4l-4-4H3a2 2 0 01-2-2v-2a2 2 0 012-2h2V8a2 2 0 012-2z"></path>
                                    </svg>
                                    Great for coordination
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    Share updates
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Ask questions
                                </span>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Extension Request Modal -->
<div id="extensionModalMyTasks" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Ajukan Perpanjangan Deadline</h3>
            <button onclick="closeExtensionModalMyTasks()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Task: <strong id="extensionTaskTitle"></strong></p>
            <p class="text-sm text-red-600 font-semibold">
                Deadline saat ini: <span id="extensionCurrentDeadline"></span>
            </p>
        </div>

        <form id="extensionFormMyTasks" onsubmit="submitExtensionRequestMyTasks(event)">
            <div class="mb-4">
                <label for="extensionRequestedDeadlineMyTasks" class="block text-sm font-medium text-gray-700 mb-2">
                    Deadline Baru <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="extensionRequestedDeadlineMyTasks" 
                    name="requested_deadline" 
                    required
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            <div class="mb-4">
                <label for="extensionReasonMyTasks" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Perpanjangan <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="extensionReasonMyTasks" 
                    name="reason" 
                    rows="4"
                    required
                    minlength="10"
                    maxlength="500"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Jelaskan alasan mengapa Anda memerlukan perpanjangan deadline (minimal 10 karakter)..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="extensionCharCount">0</span>/500 karakter (minimal 10)
                </p>
            </div>

            <div id="extensionErrorMyTasks" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button 
                    type="button" 
                    onclick="closeExtensionModalMyTasks()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-lg transition-all duration-200"
                >
                    Kirim Permintaan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Show add subtask form
function showAddSubtaskForm(taskId) {
    const form = document.getElementById(`add-subtask-form-${taskId}`);
    form.classList.remove('hidden');
    form.querySelector('input[name="title"]').focus();
}

// Hide add subtask form
function hideAddSubtaskForm(taskId) {
    const form = document.getElementById(`add-subtask-form-${taskId}`);
    form.classList.add('hidden');
    form.reset();
}

// Add new subtask
async function addSubtask(event, taskId) {
    event.preventDefault();
    
    const form = event.target;
    const title = form.querySelector('input[name="title"]').value.trim();
    
    if (!title) return;
    
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                description: '',
                priority: 'medium' // Default priority for checklist items
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Add subtask to UI
            const subtasksList = document.getElementById(`subtasks-list-${taskId}`);
            const emptyMessage = subtasksList.querySelector('.italic');
            if (emptyMessage) emptyMessage.remove();
            
            const subtaskId = data.subtask.subtask_id;
            const subtaskHtml = `
                <div class="flex items-center space-x-2 text-sm" data-subtask-id="${subtaskId}">
                    <input type="checkbox" 
                           onchange="toggleSubtask(${subtaskId})"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-gray-700">${title}</span>
                    <button onclick="deleteSubtask(${subtaskId})" 
                            class="ml-auto text-red-500 hover:text-red-700 text-xs">
                        ×
                    </button>
                </div>
            `;
            subtasksList.insertAdjacentHTML('beforeend', subtaskHtml);
            
            // Hide form and reset
            hideAddSubtaskForm(taskId);
            
            showNotification('✅ Checklist item added!', 'success');
        } else {
            throw new Error(data.message || 'Failed to add subtask');
        }
    } catch (error) {
        console.error('Error adding subtask:', error);
        showNotification('❌ Failed to add checklist item: ' + (error.message || 'Unknown error'), 'error');
    }
}

// Toggle subtask completion
async function toggleSubtask(subtaskId) {
    try {
        // Get task ID from the subtask element's parent
        const subtaskElement = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
        const taskContainer = subtaskElement.closest('[data-task-id]');
        const taskId = taskContainer.getAttribute('data-task-id');
        
        if (!taskId) {
            throw new Error('Could not find task ID');
        }
        
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Update UI based on server response
            const span = subtaskElement.querySelector('span');
            const checkbox = subtaskElement.querySelector('input[type="checkbox"]');
            
            // Use server data to determine current state
            const isCompleted = data.subtask.is_completed;
            checkbox.checked = isCompleted;
            
            if (isCompleted) {
                span.classList.add('line-through', 'text-gray-400');
                span.classList.remove('text-gray-700');
                showNotification('✅ Item completed!', 'success');
            } else {
                span.classList.remove('line-through', 'text-gray-400');
                span.classList.add('text-gray-700');
                showNotification('🔄 Item reopened', 'success');
            }
        } else {
            throw new Error(data.message || 'Failed to toggle subtask');
        }
        
    } catch (error) {
        console.error('Error toggling subtask:', error);
        
        // Revert checkbox state on error
        const subtaskElement = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
        const checkbox = subtaskElement.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        
        showNotification('❌ Failed to update item', 'error');
    }
}

// Delete subtask
async function deleteSubtask(subtaskId) {
    if (!confirm('Are you sure you want to delete this checklist item?')) {
        return;
    }
    
    try {
        // Get task ID from the subtask element's parent
        const subtaskElement = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
        const taskContainer = subtaskElement.closest('[data-task-id]');
        const taskId = taskContainer.getAttribute('data-task-id');
        
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Remove from UI
            subtaskElement.remove();
            showNotification('🗑️ Item deleted', 'success');
            
            // Check if list is empty and show empty message
            const subtasksList = document.getElementById(`subtasks-list-${taskId}`);
            const remainingItems = subtasksList.querySelectorAll('[data-subtask-id]');
            if (remainingItems.length === 0) {
                subtasksList.innerHTML = '<p class="text-xs text-gray-400 italic">No checklist items yet</p>';
            }
        } else {
            throw new Error(data.message || 'Failed to delete subtask');
        }
        
    } catch (error) {
        console.error('Error deleting subtask:', error);
        showNotification('❌ Failed to delete item: ' + (error.message || 'Unknown error'), 'error');
    }
}

// Edit subtask - show edit form
function editSubtask(subtaskId) {
    // Hide title and edit button
    const titleElement = document.getElementById(`subtask-title-${subtaskId}`);
    const editButton = document.getElementById(`edit-btn-${subtaskId}`);
    
    titleElement.classList.add('hidden');
    editButton.closest('.flex').style.display = 'none';
    
    // Show edit form
    const editForm = document.getElementById(`edit-form-${subtaskId}`);
    editForm.classList.remove('hidden');
    editForm.classList.add('flex');
    
    // Focus on input
    const input = editForm.querySelector('input[name="title"]');
    input.focus();
    input.select();
}

// Cancel edit - hide edit form
function cancelSubtaskEdit(subtaskId) {
    // Show title and edit button
    const titleElement = document.getElementById(`subtask-title-${subtaskId}`);
    const editButton = document.getElementById(`edit-btn-${subtaskId}`);
    
    titleElement.classList.remove('hidden');
    editButton.closest('.flex').style.display = '';
    
    // Hide edit form
    const editForm = document.getElementById(`edit-form-${subtaskId}`);
    editForm.classList.add('hidden');
    editForm.classList.remove('flex');
    
    // Reset form
    editForm.reset();
}

// Save subtask edit
async function saveSubtaskEdit(event, subtaskId) {
    event.preventDefault();
    
    const form = event.target;
    const title = form.querySelector('input[name="title"]').value.trim();
    
    if (!title) {
        showNotification('❌ Title cannot be empty', 'error');
        return;
    }
    
    try {
        // Get task ID from the subtask element's parent
        const subtaskElement = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
        const taskContainer = subtaskElement.closest('[data-task-id]');
        const taskId = taskContainer.getAttribute('data-task-id');
        
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: title
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Update title in UI
            const titleElement = document.getElementById(`subtask-title-${subtaskId}`);
            titleElement.textContent = title;
            
            // Hide edit form and show title
            cancelSubtaskEdit(subtaskId);
            
            showNotification('✏️ Item updated', 'success');
        } else {
            throw new Error(data.message || 'Failed to update subtask');
        }
        
    } catch (error) {
        console.error('Error updating subtask:', error);
        showNotification('❌ Failed to update item: ' + (error.message || 'Unknown error'), 'error');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// ========== EXTENSION REQUEST FUNCTIONS ==========
let currentExtensionTaskId = null;

function openExtensionModalMyTasks(taskId, taskTitle, currentDeadline) {
    currentExtensionTaskId = taskId;
    document.getElementById('extensionTaskTitle').textContent = taskTitle;
    document.getElementById('extensionCurrentDeadline').textContent = new Date(currentDeadline).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    document.getElementById('extensionReasonMyTasks').value = '';
    document.getElementById('extensionRequestedDeadlineMyTasks').value = '';
    document.getElementById('extensionErrorMyTasks').classList.add('hidden');
    document.getElementById('extensionModalMyTasks').classList.remove('hidden');
}

function closeExtensionModalMyTasks() {
    document.getElementById('extensionModalMyTasks').classList.add('hidden');
    currentExtensionTaskId = null;
}

async function submitExtensionRequestMyTasks(event) {
    event.preventDefault();
    
    const reason = document.getElementById('extensionReasonMyTasks').value.trim();
    const requestedDeadline = document.getElementById('extensionRequestedDeadlineMyTasks').value;
    
    if (reason.length < 10) {
        showExtensionError('Alasan minimal 10 karakter');
        return;
    }
    
    if (!requestedDeadline) {
        showExtensionError('Deadline baru harus diisi');
        return;
    }
    
    try {
        const response = await fetch('/extension-requests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                task_id: currentExtensionTaskId,
                reason: reason,
                requested_deadline: requestedDeadline
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            closeExtensionModalMyTasks();
            showNotification('✅ Permintaan perpanjangan berhasil dikirim ke leader!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showExtensionError(data.message || 'Gagal mengirim permintaan');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showExtensionError('Terjadi kesalahan saat mengirim permintaan');
    }
}

function showExtensionError(message) {
    const errorDiv = document.getElementById('extensionErrorMyTasks');
    errorDiv.querySelector('p').textContent = message;
    errorDiv.classList.remove('hidden');
}

// Character counter for extension reason
document.addEventListener('DOMContentLoaded', function() {
    const reasonTextarea = document.getElementById('extensionReasonMyTasks');
    const charCount = document.getElementById('extensionCharCount');
    
    if (reasonTextarea && charCount) {
        reasonTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeExtensionModalMyTasks();
        }
    });
    
    // Close modal on outside click
    const modal = document.getElementById('extensionModalMyTasks');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                closeExtensionModalMyTasks();
            }
        });
    }
});
</script>

{{-- Work Timer Script --}}
<script src="{{ asset('js/work-timer.js') }}"></script>

{{-- Project Discussion Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Discussion Variables
    let currentProjectDiscussionId = null;
    
    // Initialize project discussion
    initializeProjectDiscussion();
    
    function initializeProjectDiscussion() {
        // Get first project tab
        const firstTab = document.querySelector('.project-discussion-tab');
        if (firstTab) {
            currentProjectDiscussionId = firstTab.getAttribute('data-project-id');
            loadProjectComments(currentProjectDiscussionId);
        }
        
        // Handle tab switching
        document.querySelectorAll('.project-discussion-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.project-discussion-tab').forEach(t => {
                    t.classList.remove('border-blue-500', 'text-blue-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.add('border-blue-500', 'text-blue-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                
                // Load comments for selected project
                currentProjectDiscussionId = this.getAttribute('data-project-id');
                loadProjectComments(currentProjectDiscussionId);
            });
        });
        
        // Handle comment form submission
        const commentForm = document.getElementById('project-comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!currentProjectDiscussionId) {
                    showNotification('❌ Please select a project first', 'error');
                    return;
                }
                
                const comment = document.getElementById('project-comment').value.trim();
                if (!comment) {
                    showNotification('❌ Please enter a comment', 'error');
                    return;
                }
                
                addProjectComment(currentProjectDiscussionId, comment);
            });
        }
        
        // Character counter for comment
        const commentTextarea = document.getElementById('project-comment');
        const charCount = document.getElementById('comment-char-count');
        if (commentTextarea && charCount) {
            commentTextarea.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }
    }
    
    // Load comments for a project
    function loadProjectComments(projectId) {
        const container = document.getElementById('project-comments-container');
        
        // Show loading
        container.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-500">Loading discussions...</span>
            </div>
        `;
        
        fetch(`/projects/${projectId}/comments`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayProjectComments(data.comments);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <div class="text-red-500 mb-2">❌ Error loading comments</div>
                            <p class="text-gray-500 text-sm">${data.error || 'Unknown error'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-red-500 mb-2">❌ Failed to load discussions</div>
                        <p class="text-gray-500 text-sm">Please check your connection and try again</p>
                        <button onclick="loadProjectComments(${projectId})" class="mt-2 px-3 py-1 text-sm bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200">Retry</button>
                    </div>
                `;
            });
    }
    
    // Display comments
    function displayProjectComments(comments) {
        const container = document.getElementById('project-comments-container');
        
        if (comments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No discussions yet</h3>
                    <p class="text-gray-500">Be the first to start a conversation about this project!</p>
                </div>
            `;
            return;
        }
        
        const commentsHtml = comments.map(comment => `
            <div class="flex space-x-4 p-4 bg-white border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r ${getAvatarGradient(comment.user.role)} flex items-center justify-center text-white font-bold text-sm">
                        ${comment.user.initials}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-1">
                        <h4 class="font-medium text-gray-900">${comment.user.name}</h4>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRoleBadgeClass(comment.user.role)}">
                            ${formatRole(comment.user.role)}
                        </span>
                        <span class="text-xs text-gray-500">${comment.created_at_human}</span>
                    </div>
                    <div class="text-gray-700 whitespace-pre-wrap break-words">${comment.comment}</div>
                    ${comment.is_owner ? `
                        <div class="mt-2">
                            <button onclick="deleteProjectComment(${comment.comment_id})" 
                                    class="text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                🗑️ Delete
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        container.innerHTML = `
            <div class="space-y-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-800">Discussion (${comments.length} messages)</h3>
                    <button onclick="loadProjectComments(${currentProjectDiscussionId})" class="text-xs text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
                <div class="max-h-96 overflow-y-auto space-y-3">
                    ${commentsHtml}
                </div>
            </div>
        `;
    }
    
    // Add comment
    function addProjectComment(projectId, comment) {
        const submitBtn = document.querySelector('#project-comment-form button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
        `;
        
        fetch(`/projects/${projectId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ comment: comment })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('project-comment').value = '';
                document.getElementById('comment-char-count').textContent = '0';
                loadProjectComments(projectId);
                showNotification('✅ Message sent successfully!', 'success');
            } else {
                showNotification('❌ ' + (data.error || 'Failed to send message'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('❌ Error sending message. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }
    
    // Delete comment
    window.deleteProjectComment = function(commentId) {
        if (!confirm('Are you sure you want to delete this message?')) return;
        
        fetch(`/projects/${currentProjectDiscussionId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadProjectComments(currentProjectDiscussionId);
                showNotification('🗑️ Message deleted', 'success');
            } else {
                showNotification('❌ Failed to delete message', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('❌ Error deleting message', 'error');
        });
    }
    
    // Helper functions
    function getAvatarGradient(role) {
        switch(role) {
            case 'admin': return 'from-red-500 to-pink-600';
            case 'leader': return 'from-purple-500 to-indigo-600';
            case 'developer': return 'from-blue-500 to-cyan-600';
            case 'designer': return 'from-pink-500 to-rose-600';
            default: return 'from-gray-500 to-gray-600';
        }
    }
    
    function getRoleBadgeClass(role) {
        switch(role) {
            case 'admin': return 'bg-red-100 text-red-800';
            case 'leader': return 'bg-purple-100 text-purple-800';
            case 'developer': return 'bg-blue-100 text-blue-800';
            case 'designer': return 'bg-pink-100 text-pink-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function formatRole(role) {
        const roleMap = {
            'admin': '👑 Admin',
            'leader': '👨‍💼 Leader',
            'developer': '💻 Developer',
            'designer': '🎨 Designer'
        };
        return roleMap[role] || role;
    }
});
</script>
@endpush
@endsection