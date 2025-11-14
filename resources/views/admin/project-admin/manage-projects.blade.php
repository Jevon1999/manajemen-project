@extends('layout.app')

@section('title', 'Manage Projects')

@section('page-title', 'Project Management')
@section('page-description', 'Create and delete projects, manage project lifecycle')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Total Projects</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_projects'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Active</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $statistics['active_projects'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Completed</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $statistics['completed_projects'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Planning</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $statistics['planning_projects'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <div class="flex space-x-4">
            <button onclick="showCreateProjectModal()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create New Project
            </button>
            
            <button onclick="toggleBulkActions()" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Bulk Actions
            </button>
        </div>
        
        <div class="flex space-x-2">
            <select onchange="filterProjects(this.value)" class="border-gray-300 rounded-md shadow-sm text-sm">
                <option value="">All Status</option>
                <option value="planning">Planning</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
            </select>
        </div>
    </div>

    <!-- Bulk Actions Panel -->
    <div id="bulkActionsPanel" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-yellow-800">
                    <span id="selectedCount">0</span> projects selected
                </span>
                <button onclick="selectAllProjects()" class="text-sm text-yellow-600 hover:text-yellow-800">
                    Select All
                </button>
                <button onclick="deselectAllProjects()" class="text-sm text-yellow-600 hover:text-yellow-800">
                    Deselect All
                </button>
            </div>
            <div class="flex space-x-2">
                <button onclick="bulkUpdateStatus()" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                    Update Status
                </button>
                <button onclick="bulkDeleteProjects()" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm">
                    Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Projects</h3>
        </div>
        
        <div id="projectsList" class="divide-y divide-gray-200">
            @foreach($projects as $project)
            <div class="p-6 project-item" data-status="{{ $project->status }}" data-project-id="{{ $project->project_id }}">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <input type="checkbox" class="project-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                               value="{{ $project->project_id }}" onchange="updateSelectedCount()">
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $project->project_name }}</h4>
                                <p class="text-gray-600 text-sm mb-3">{{ $project->description ?: 'No description' }}</p>
                                
                                <div class="flex items-center space-x-6 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $project->creator->full_name ?? 'Unknown' }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                        {{ $project->members_count }} Members
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                        {{ $project->cards_count }} Tasks
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        Created {{ $project->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                @php
                                    $statusColors = [
                                        'planning' => 'bg-yellow-100 text-yellow-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'on-hold' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusTexts = [
                                        'planning' => 'Planning',
                                        'active' => 'Active',
                                        'completed' => 'Completed',
                                        'on-hold' => 'On Hold'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusTexts[$project->status] ?? $project->status }}
                                </span>
                                
                                <div class="flex space-x-1">
                                    <button onclick="showProjectDetails({{ $project->project_id }})" 
                                            class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    
                                    <button onclick="editProject({{ $project->project_id }})" 
                                            class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    
                                    <button onclick="deleteProject({{ $project->project_id }}, '{{ $project->project_name }}')" 
                                            class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Include modals and scripts here -->
@include('admin.project-admin.modals.create-project')
@include('admin.project-admin.modals.project-details')

<script>
let bulkActionsVisible = false;

function toggleBulkActions() {
    bulkActionsVisible = !bulkActionsVisible;
    const panel = document.getElementById('bulkActionsPanel');
    if (bulkActionsVisible) {
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
        deselectAllProjects();
    }
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
}

function selectAllProjects() {
    document.querySelectorAll('.project-checkbox').forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function deselectAllProjects() {
    document.querySelectorAll('.project-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

function filterProjects(status) {
    const projects = document.querySelectorAll('.project-item');
    projects.forEach(project => {
        if (status === '' || project.dataset.status === status) {
            project.style.display = 'block';
        } else {
            project.style.display = 'none';
        }
    });
}

function deleteProject(projectId, projectName) {
    if (confirm(`Are you sure you want to delete "${projectName}"? This action cannot be undone.`)) {
        fetch(`/admin/project-admin/delete-project/${projectId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the project');
        });
    }
}

function bulkDeleteProjects() {
    const selected = Array.from(document.querySelectorAll('.project-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select projects to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} projects? This action cannot be undone.`)) {
        // Implement bulk delete logic here
        console.log('Bulk delete:', selected);
    }
}

function showProjectDetails(projectId) {
    // Implement project details modal
    window.open(`/projects/${projectId}`, '_blank');
}

function editProject(projectId) {
    // Implement edit project functionality
    console.log('Edit project:', projectId);
}
</script>
@endsection