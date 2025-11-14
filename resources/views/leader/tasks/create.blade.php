@extends('layout.app')

@section('title', 'Create New Task')

@section('page-title', 'Create New Task')
@section('page-description', 'Assign a new task to your team members in ' . $project->project_name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Create New Task</h1>
                    <p class="mt-1 text-sm text-gray-600">Project: {{ $project->project_name }}</p>
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

        <form action="{{ route('leader.tasks.store', $project->project_id) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Task Basic Information -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700">Task Title</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title') }}"
                           maxlength="100"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-300 @enderror"
                           placeholder="Enter a clear, descriptive task title">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="board_id" class="block text-sm font-medium text-gray-700">Board</label>
                    <select name="board_id" 
                            id="board_id" 
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('board_id') border-red-300 @enderror">
                        <option value="">Select a board</option>
                        @foreach($project->boards as $board)
                            <option value="{{ $board->board_id }}" {{ old('board_id') == $board->board_id ? 'selected' : '' }}>
                                {{ $board->board_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('board_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" 
                            id="priority" 
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('priority') border-red-300 @enderror">
                        <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>🟢 Low Priority</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>🟡 Medium Priority</option>
                        <option value="high" {{ old('priority', 'medium') == 'high' ? 'selected' : '' }}>🔴 High Priority</option>
                        <option value="critical" {{ old('priority', 'medium') == 'critical' ? 'selected' : '' }}>🚨 Critical Priority</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Initial Status</label>
                    <select name="status" 
                            id="status" 
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-300 @enderror">
                        <option value="todo" {{ old('status', 'todo') == 'todo' ? 'selected' : '' }}>📋 To Do</option>
                        <option value="in_progress" {{ old('status', 'todo') == 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                        <option value="review" {{ old('status', 'todo') == 'review' ? 'selected' : '' }}>👀 Review</option>
                        <option value="done" {{ old('status', 'todo') == 'done' ? 'selected' : '' }}>✅ Done</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date (Optional)</label>
                    <input type="date" 
                           name="due_date" 
                           id="due_date" 
                           value="{{ old('due_date') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('due_date') border-red-300 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Task Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="4" 
                          maxlength="1000"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-300 @enderror"
                          placeholder="Provide detailed task requirements, acceptance criteria, and any relevant context...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Character count: <span id="desc-count">0</span>/1000</p>
            </div>

            <!-- Time Estimation -->
            <div>
                <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Estimated Hours (Optional)</label>
                <input type="number" 
                       name="estimated_hours" 
                       id="estimated_hours" 
                       value="{{ old('estimated_hours') }}"
                       min="0.1" 
                       max="999.99" 
                       step="0.1"
                       class="mt-1 block w-full sm:w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('estimated_hours') border-red-300 @enderror"
                       placeholder="8.0">
                @error('estimated_hours')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Help your team plan better by providing an estimate</p>
            </div>

            <!-- Team Member Assignment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Assign to Team Members</label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($teamMembers as $member)
                        <label class="relative flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="assigned_users[]" 
                                       value="{{ $member->user->user_id }}"
                                       {{ is_array(old('assigned_users')) && in_array($member->user->user_id, old('assigned_users')) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </div>
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
                                            @else
                                                👤 {{ ucfirst($member->role) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-full">
                            <div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No team members found</h3>
                                <p class="mt-1 text-sm text-gray-500">Add developers or designers to this project first.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                @error('assigned_users')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('leader.projects.show', $project->project_id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create & Assign Task
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character count for description
    const descriptionField = document.getElementById('description');
    const countDisplay = document.getElementById('desc-count');
    
    function updateCount() {
        const length = descriptionField.value.length;
        countDisplay.textContent = length;
        countDisplay.className = length > 900 ? 'text-red-500' : 'text-gray-500';
    }
    
    descriptionField.addEventListener('input', updateCount);
    updateCount(); // Initial count
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('input[name="assigned_users[]"]:checked');
        
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('Please assign the task to at least one team member.');
            return false;
        }
    });
});
</script>
@endpush
@endsection