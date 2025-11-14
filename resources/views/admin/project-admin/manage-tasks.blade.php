@extends('layout.app')

@section('title', 'Task Management')

@section('page-title', 'Task Management')
@section('page-description', 'Comprehensive task management across all projects')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Total Tasks</h3>
                    <p class="text-2xl font-bold text-gray-900" id="totalTasks">0</p>
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
                    <h3 class="text-sm font-medium text-gray-900">Pending</h3>
                    <p class="text-2xl font-bold text-yellow-600" id="pendingTasks">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">In Progress</h3>
                    <p class="text-2xl font-bold text-orange-600" id="inProgressTasks">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Review</h3>
                    <p class="text-2xl font-bold text-purple-600" id="reviewTasks">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Completed</h3>
                    <p class="text-2xl font-bold text-green-600" id="completedTasks">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Project:</label>
                    <select id="projectFilter" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Projects</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Status:</label>
                    <select id="statusFilter" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Priority:</label>
                    <select id="priorityFilter" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <button onclick="exportTasks()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </button>
                
                <button onclick="showCreateTaskModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Task
                </button>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Tasks</h3>
            <p class="text-sm text-gray-500">Manage and monitor tasks across all projects</p>
        </div>
        
        <div id="tasksContainer">
            <div class="p-8 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-gray-500">Loading tasks...</p>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div id="taskDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="taskDetailsTitle">Task Details</h3>
                        <button onclick="hideTaskDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4" id="taskDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTaskStatistics();
    loadProjects();
    loadTasks();
    
    // Add event listeners for filters
    document.getElementById('projectFilter').addEventListener('change', loadTasks);
    document.getElementById('statusFilter').addEventListener('change', loadTasks);
    document.getElementById('priorityFilter').addEventListener('change', loadTasks);
});

function loadTaskStatistics() {
    fetch('/admin/project-admin/task-statistics')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalTasks').textContent = data.statistics.total || 0;
            document.getElementById('pendingTasks').textContent = data.statistics.todo || 0;
            document.getElementById('inProgressTasks').textContent = data.statistics.in_progress || 0;
            document.getElementById('reviewTasks').textContent = data.statistics.review || 0;
            document.getElementById('completedTasks').textContent = data.statistics.done || 0;
        }
    })
    .catch(error => console.error('Error loading statistics:', error));
}

function loadProjects() {
    fetch('/admin/project-admin/projects-list')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const projectFilter = document.getElementById('projectFilter');
            data.projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.project_id;
                option.textContent = project.project_name;
                projectFilter.appendChild(option);
            });
        }
    })
    .catch(error => console.error('Error loading projects:', error));
}

function loadTasks() {
    const projectId = document.getElementById('projectFilter').value;
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    
    const params = new URLSearchParams();
    if (projectId) params.append('project_id', projectId);
    if (status) params.append('status', status);
    if (priority) params.append('priority', priority);
    
    fetch(`/admin/project-admin/tasks?${params.toString()}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTasks(data.tasks);
        } else {
            document.getElementById('tasksContainer').innerHTML = `
                <div class="p-8 text-center">
                    <p class="text-red-600">Failed to load tasks</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading tasks:', error);
        document.getElementById('tasksContainer').innerHTML = `
            <div class="p-8 text-center">
                <p class="text-red-600">Error loading tasks</p>
            </div>
        `;
    });
}

function displayTasks(tasks) {
    const container = document.getElementById('tasksContainer');
    
    if (tasks.length === 0) {
        container.innerHTML = `
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Tasks Found</h3>
                <p class="text-gray-600">No tasks match the current filters</p>
            </div>
        `;
        return;
    }
    
    const statusColors = {
        'todo': 'bg-gray-100 text-gray-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'review': 'bg-purple-100 text-purple-800',
        'done': 'bg-green-100 text-green-800'
    };
    
    const priorityColors = {
        'low': 'bg-blue-100 text-blue-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'critical': 'bg-red-100 text-red-800'
    };
    
    const tasksHtml = tasks.map(task => `
        <div class="p-6 border-b border-gray-200 hover:bg-gray-50">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-gray-900 mb-2">${task.title}</h4>
                            <p class="text-gray-600 text-sm mb-3">${task.description || 'No description'}</p>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    ${task.project_name}
                                </span>
                                ${task.assigned_users && task.assigned_users.length > 0 ? `
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    ${task.assigned_users.map(u => u.full_name).join(', ')}
                                </span>
                                ` : ''}
                                ${task.due_date ? `
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4a1 1 0 001 1h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h3a1 1 0 001-1z"/>
                                    </svg>
                                    ${new Date(task.due_date).toLocaleDateString()}
                                </span>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-2 ml-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[task.status] || 'bg-gray-100 text-gray-800'}">
                                ${task.status.replace('_', ' ')}
                            </span>
                            ${task.priority ? `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${priorityColors[task.priority] || 'bg-gray-100 text-gray-800'}">
                                ${task.priority}
                            </span>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="mt-4 flex space-x-3">
                        <button onclick="showTaskDetails(${task.card_id})" 
                                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            View Details
                        </button>
                        
                        <button onclick="editTask(${task.card_id})" 
                                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        
                        <button onclick="reassignTask(${task.card_id})" 
                                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Reassign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = '<div class="divide-y divide-gray-200">' + tasksHtml + '</div>';
}

function showTaskDetails(taskId) {
    document.getElementById('taskDetailsModal').classList.remove('hidden');
    // Load task details via AJAX
    loadTaskDetails(taskId);
}

function hideTaskDetailsModal() {
    document.getElementById('taskDetailsModal').classList.add('hidden');
}

function loadTaskDetails(taskId) {
    document.getElementById('taskDetailsContent').innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-gray-500">Loading task details...</p>
        </div>
    `;
    
    // Implementation would fetch task details from API
    // For now, show placeholder
    setTimeout(() => {
        document.getElementById('taskDetailsContent').innerHTML = `
            <p class="text-gray-600">Task details will be implemented here.</p>
        `;
    }, 1000);
}

function editTask(taskId) {
    // Implementation for editing task
    console.log('Edit task:', taskId);
}

function reassignTask(taskId) {
    // Implementation for reassigning task
    console.log('Reassign task:', taskId);
}

function exportTasks() {
    // Implementation for exporting tasks
    console.log('Export tasks');
}

function showCreateTaskModal() {
    // Implementation for creating new task
    console.log('Create new task');
}
</script>
@endsection