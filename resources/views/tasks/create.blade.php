@extends('layout.app')

@section('title', 'Create New Task')

@section('page-title', 'Create New Task')
@section('page-description', $project->project_name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Create New Task</h2>
            <p class="text-gray-600">Add a new task to {{ $project->project_name }}</p>
        </div>

        <form action="{{ route('admin.projects.tasks.store', $project->project_id) }}" method="POST" id="taskForm">
            @csrf
            
            <!-- Hidden Status Field -->
            <input type="hidden" name="status" value="todo">
            
            <!-- Title -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Task Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       value="{{ old('title') }}"
                       required
                       maxlength="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                       placeholder="Enter task title">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" 
                          rows="4"
                          required
                          maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="Describe the task requirements and acceptance criteria...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Character count: <span id="desc-count">0</span>/1000</p>
            </div>

            <!-- Priority and Deadline Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-500 @enderror">
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>🟢 Low</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🔴 High</option>
                        <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>🚨 Critical</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Deadline <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="deadline" 
                           value="{{ old('deadline') }}"
                           required
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('deadline') border-red-500 @enderror">
                    @error('deadline')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- User Assignment Section -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assign To <span class="text-xs text-gray-500">(Select team member)</span>
                </label>
                
                <select name="assigned_to" 
                        id="assigned_to_select"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('assigned_to') border-red-500 @enderror">
                    <option value="">Not Assigned</option>
                    @forelse($allMembers as $member)
                        @php
                            $hasActiveTask = isset($memberActiveTasks[$member->user->user_id]);
                            $activeTask = $hasActiveTask ? $memberActiveTasks[$member->user->user_id] : null;
                        @endphp
                        <option value="{{ $member->user->user_id }}" 
                                {{ old('assigned_to') == $member->user->user_id ? 'selected' : '' }}
                                data-has-task="{{ $hasActiveTask ? 'true' : 'false' }}"
                                data-task-title="{{ $hasActiveTask ? $activeTask->title : '' }}"
                                data-task-status="{{ $hasActiveTask ? $activeTask->status : '' }}"
                                data-task-project="{{ $hasActiveTask ? $activeTask->project->project_name : '' }}">
                            {{ $member->user->full_name }} 
                            @if($member->role === 'developer')
                                (💻 Developer)
                            @elseif($member->role === 'designer') 
                                (🎨 Designer)
                            @else
                                (👤 {{ ucfirst($member->role) }})
                            @endif
                            @if($hasActiveTask)
                                - 🔴 HAS ACTIVE TASK
                            @else
                                - ✅ Available
                            @endif
                        </option>
                    @empty
                        <option value="" disabled>No team members available</option>
                    @endforelse
                </select>
                
                <!-- Active Task Warning -->
                <div id="active-task-warning" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-amber-800">⚠️ User Already Has Active Task</h4>
                            <p class="text-sm text-amber-700 mt-1">
                                <strong id="active-task-title"></strong> 
                                (<span id="active-task-status"></span>)
                                <br>
                                Project: <span id="active-task-project"></span>
                            </p>
                            <p class="text-xs text-amber-600 mt-2">
                                💡 Tip: User dapat mengerjakan task baru setelah task saat ini selesai atau dipindahkan.
                            </p>
                        </div>
                    </div>
                </div>
                
                @error('assigned_to')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                @if($allMembers->isEmpty())
                    <p class="mt-2 text-sm text-amber-600">
                        ⚠️ No designers or developers found in this project. Please add team members first.
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-600">
                        ℹ️ Available team members: {{ $allMembers->count() }} (designers & developers only)
                    </p>
                @endif
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character count for description
    const descriptionField = document.querySelector('textarea[name="description"]');
    const countDisplay = document.getElementById('desc-count');
    
    function updateCount() {
        const length = descriptionField.value.length;
        countDisplay.textContent = length;
        countDisplay.className = length > 900 ? 'text-red-500 text-xs' : 'text-gray-500 text-xs';
    }
    
    descriptionField.addEventListener('input', updateCount);
    updateCount(); // Initial count
    
    // Handle user selection - show active task warning
    const assignSelect = document.getElementById('assigned_to_select');
    const warningDiv = document.getElementById('active-task-warning');
    const taskTitleSpan = document.getElementById('active-task-title');
    const taskStatusSpan = document.getElementById('active-task-status');
    const taskProjectSpan = document.getElementById('active-task-project');
    
    assignSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const hasTask = selectedOption.getAttribute('data-has-task') === 'true';
        
        if (hasTask) {
            // Show warning
            taskTitleSpan.textContent = selectedOption.getAttribute('data-task-title');
            taskStatusSpan.textContent = selectedOption.getAttribute('data-task-status').toUpperCase();
            taskProjectSpan.textContent = selectedOption.getAttribute('data-task-project');
            warningDiv.classList.remove('hidden');
        } else {
            // Hide warning
            warningDiv.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
