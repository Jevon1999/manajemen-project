@extends('layout.app')

@section('title', 'Task Details - ' . $task->card_title)

@section('page-title', $task->card_title)
@section('page-description', 'Manage task details and assignments')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Task Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Task Header -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h1 class="text-xl font-semibold text-gray-900">{{ $task->card_title }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->priority === 'critical') bg-red-100 text-red-800
                                @elseif($task->priority === 'high') bg-orange-100 text-orange-800  
                                @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                @if($task->priority === 'critical') 🚨
                                @elseif($task->priority === 'high') 🔴
                                @elseif($task->priority === 'medium') 🟡
                                @else 🟢 @endif
                                {{ ucfirst($task->priority) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->status === 'done') bg-green-100 text-green-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($task->status === 'review') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($task->status === 'done') ✅
                                @elseif($task->status === 'in_progress') ⚡
                                @elseif($task->status === 'review') 👀
                                @else 📋 @endif
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                        <a href="{{ route('leader.projects.show', $project->project_id) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Project
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Task Information Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Board</div>
                            <div class="mt-1 text-sm font-medium text-gray-900">{{ $task->board->board_name }}</div>
                        </div>
                        
                        @if($task->due_date)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Due Date</div>
                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'done')
                                    <span class="ml-1 text-red-600">(Overdue)</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($task->estimated_hours)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Estimated</div>
                            <div class="mt-1 text-sm font-medium text-gray-900">{{ $task->estimated_hours }} hours</div>
                        </div>
                        @endif
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Created</div>
                            <div class="mt-1 text-sm font-medium text-gray-900">{{ $task->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>

                    <!-- Task Description -->
                    @if($task->description)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Description</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $task->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Assigned Team Members -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-900">Assigned Team Members</h3>
                            <button id="reassign-btn" 
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reassign
                            </button>
                        </div>
                        <div class="space-y-3">
                            @forelse($task->assignments as $assignment)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ substr($assignment->user->full_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $assignment->user->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $assignment->user->email }}</p>
                                </div>
                                <div class="ml-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($assignment->assignment_status) }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">
                                <p class="text-sm text-gray-500">No team members assigned to this task</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtasks Section -->
            @if($task->subtasks->isNotEmpty())
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Subtasks</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($task->subtasks as $subtask)
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                            <div class="flex-shrink-0">
                                <input type="checkbox" 
                                       {{ $subtask->status === 'done' ? 'checked' : '' }}
                                       disabled
                                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 {{ $subtask->status === 'done' ? 'line-through' : '' }}">
                                    {{ $subtask->subtaks_title }}
                                </p>
                                @if($subtask->description)
                                <p class="text-xs text-gray-500 mt-1">{{ $subtask->description }}</p>
                                @endif
                            </div>
                            <div class="ml-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($subtask->status === 'done') bg-green-100 text-green-800
                                    @elseif($subtask->status === 'in_progress') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $subtask->status)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <!-- Priority & Status Update -->
                    <div class="space-y-3">
                        <div>
                            <label for="priority-select" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority-select" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>🟢 Low</option>
                                <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>🔴 High</option>
                                <option value="critical" {{ $task->priority === 'critical' ? 'selected' : '' }}>🚨 Critical</option>
                            </select>
                        </div>
                        <div>
                            <label for="status-select" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status-select" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>📋 To Do</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>👀 Review</option>
                                <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>✅ Done</option>
                            </select>
                        </div>
                        <button id="update-btn" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Update Task
                        </button>
                    </div>
                </div>
            </div>

            <!-- Project Info -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Project Info</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Project Name</dt>
                            <dd class="text-sm text-gray-900">{{ $project->project_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Team Members</dt>
                            <dd class="text-sm text-gray-900">{{ $teamMembers->count() }} members</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Project Status</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reassignment Modal -->
<div id="reassign-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Reassign Task</h3>
                <div class="space-y-3">
                    @foreach($teamMembers as $member)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" 
                               name="reassign_users[]" 
                               value="{{ $member->user->user_id }}"
                               {{ $task->assignments->contains('user_id', $member->user->user_id) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <div class="ml-3 flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ substr($member->user->full_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $member->user->full_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if($member->role === 'developer')
                                            💻 Developer
                                        @elseif($member->role === 'designer')
                                            🎨 Designer
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button id="confirm-reassign" 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Reassign Task
                </button>
                <button id="cancel-reassign" 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const prioritySelect = document.getElementById('priority-select');
    const statusSelect = document.getElementById('status-select');
    const updateBtn = document.getElementById('update-btn');
    const reassignBtn = document.getElementById('reassign-btn');
    const reassignModal = document.getElementById('reassign-modal');
    const confirmReassignBtn = document.getElementById('confirm-reassign');
    const cancelReassignBtn = document.getElementById('cancel-reassign');

    // Update priority and status
    updateBtn.addEventListener('click', function() {
        const data = {
            priority: prioritySelect.value,
            status: statusSelect.value,
            _token: '{{ csrf_token() }}'
        };

        fetch(`{{ route('leader.tasks.update-priority-status', [$project->project_id, $task->card_id]) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating task');
        });
    });

    // Show reassignment modal
    reassignBtn.addEventListener('click', function() {
        reassignModal.classList.remove('hidden');
    });

    // Hide reassignment modal
    function hideModal() {
        reassignModal.classList.add('hidden');
    }

    cancelReassignBtn.addEventListener('click', hideModal);
    
    // Close modal when clicking backdrop
    reassignModal.addEventListener('click', function(e) {
        if (e.target === reassignModal) {
            hideModal();
        }
    });

    // Confirm reassignment
    confirmReassignBtn.addEventListener('click', function() {
        const selectedUsers = Array.from(document.querySelectorAll('input[name="reassign_users[]"]:checked'))
            .map(checkbox => checkbox.value);

        if (selectedUsers.length === 0) {
            alert('Please select at least one team member');
            return;
        }

        const data = {
            assigned_users: selectedUsers,
            _token: '{{ csrf_token() }}'
        };

        fetch(`{{ route('leader.tasks.reassign', [$project->project_id, $task->card_id]) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error reassigning task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error reassigning task');
        });
    });
});
</script>
@endpush
@endsection