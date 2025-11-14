@extends('layout.app')

@section('title', 'Bulk Operations Management')

@section('page-title', 'Bulk Operations Management')
@section('page-description', 'Perform batch operations on projects, users, and tasks')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Operation Selection Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('projects')" id="tab-projects" 
                        class="tab-button border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                    Project Operations
                </button>
                <button onclick="showTab('users')" id="tab-users" 
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    User Operations
                </button>
                <button onclick="showTab('tasks')" id="tab-tasks" 
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Task Operations
                </button>
                <button onclick="showTab('export')" id="tab-export" 
                        class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Data Export
                </button>
            </nav>
        </div>

        <!-- Projects Tab -->
        <div id="content-projects" class="tab-content p-6">
            <div class="space-y-6">
                <!-- Project Status Update -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Update Project Status</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Projects</label>
                            <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto" id="projectsList">
                                <div class="text-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                                    <p class="mt-2 text-sm text-gray-500">Loading projects...</p>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-between text-sm text-gray-500">
                                <button onclick="selectAllProjects()" class="text-blue-600 hover:text-blue-800">Select All</button>
                                <button onclick="clearProjectSelection()" class="text-red-600 hover:text-red-800">Clear Selection</button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                            <select id="newProjectStatus" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Status</option>
                                <option value="planning">Planning</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                            
                            <div class="mt-4">
                                <button onclick="bulkUpdateProjectStatus()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Update Project Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Delete Projects -->
                <div class="border border-red-200 rounded-lg p-6 bg-red-50">
                    <h3 class="text-lg font-medium text-red-900 mb-4">Bulk Delete Projects</h3>
                    <p class="text-sm text-red-700 mb-4">⚠️ Warning: This action cannot be undone. All project data including tasks, comments, and time logs will be permanently deleted.</p>
                    
                    <button onclick="bulkDeleteProjects()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Selected Projects
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="content-users" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <!-- Bulk User Assignment -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Assign Users to Projects</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Users</label>
                            <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto" id="usersList">
                                <div class="text-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                                    <p class="mt-2 text-sm text-gray-500">Loading users...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Projects</label>
                            <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto" id="projectsListForUsers">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select id="userRole" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 mb-4">
                                <option value="member">Member</option>
                                <option value="team_lead">Team Lead</option>
                                <option value="project_manager">Project Manager</option>
                            </select>
                            
                            <button onclick="bulkAssignUsers()" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Assign Users
                            </button>
                            
                            <button onclick="bulkRemoveUsers()" 
                                    class="w-full mt-2 inline-flex justify-center items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z"/>
                                </svg>
                                Remove Users
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Tab -->
        <div id="content-tasks" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <!-- Task Status Update -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Update Task Status</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tasks</label>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <select id="taskProjectFilter" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Projects</option>
                                </select>
                                <select id="taskStatusFilter" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Status</option>
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            
                            <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto" id="tasksList">
                                <div class="text-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                                    <p class="mt-2 text-sm text-gray-500">Loading tasks...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                                <select id="newTaskStatus" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Status</option>
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Priority</label>
                                <select id="newTaskPriority" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <button onclick="bulkUpdateTaskStatus()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Update Status
                                </button>
                                
                                <button onclick="bulkUpdateTaskPriority()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    Update Priority
                                </button>
                                
                                <button onclick="bulkDeleteTasks()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Delete Tasks
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Tab -->
        <div id="content-export" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Export Data</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <select id="exportFormat" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 mb-4">
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                            
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Projects to Export</label>
                            <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto" id="exportProjectsList">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>
                        
                        <div>
                            <button onclick="exportProjectData()" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Tracking -->
    <div id="progressContainer" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Operation Progress</h3>
        <div class="space-y-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span id="progressText">Starting operation...</span>
                <span id="progressPercent">0%</span>
            </div>
            <div id="progressErrors" class="text-red-600 text-sm hidden"></div>
        </div>
    </div>
</div>

<script>
let selectedProjects = [];
let selectedUsers = [];
let selectedTasks = [];

document.addEventListener('DOMContentLoaded', function() {
    loadInitialData();
});

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');
    document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById('tab-' + tabName).classList.add('border-blue-500', 'text-blue-600');
}

function loadInitialData() {
    loadProjects();
    loadUsers();
    loadTasks();
}

function loadProjects() {
    fetch('/admin/project-admin/projects-list')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayProjects(data.projects);
            populateExportProjects(data.projects);
            populateProjectFilters(data.projects);
        }
    })
    .catch(error => console.error('Error loading projects:', error));
}

function loadUsers() {
    fetch('/admin/users')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayUsers(data.users);
        }
    })
    .catch(error => console.error('Error loading users:', error));
}

function loadTasks() {
    fetch('/admin/project-admin/tasks')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTasks(data.tasks);
        }
    })
    .catch(error => console.error('Error loading tasks:', error));
}

function displayProjects(projects) {
    const container = document.getElementById('projectsList');
    const userProjectsContainer = document.getElementById('projectsListForUsers');
    
    const html = projects.map(project => `
        <label class="flex items-center p-2 hover:bg-gray-50 rounded">
            <input type="checkbox" class="project-checkbox" value="${project.project_id}" 
                   onchange="updateProjectSelection(${project.project_id}, this.checked)">
            <span class="ml-2 text-sm">${project.project_name}</span>
        </label>
    `).join('');
    
    container.innerHTML = html;
    userProjectsContainer.innerHTML = html;
}

function displayUsers(users) {
    const container = document.getElementById('usersList');
    
    const html = users.map(user => `
        <label class="flex items-center p-2 hover:bg-gray-50 rounded">
            <input type="checkbox" class="user-checkbox" value="${user.user_id}" 
                   onchange="updateUserSelection(${user.user_id}, this.checked)">
            <span class="ml-2 text-sm">${user.full_name} (${user.email})</span>
        </label>
    `).join('');
    
    container.innerHTML = html;
}

function displayTasks(tasks) {
    const container = document.getElementById('tasksList');
    
    const html = tasks.map(task => `
        <label class="flex items-center p-2 hover:bg-gray-50 rounded">
            <input type="checkbox" class="task-checkbox" value="${task.card_id}" 
                   onchange="updateTaskSelection(${task.card_id}, this.checked)">
            <div class="ml-2 flex-1">
                <span class="text-sm font-medium">${task.title}</span>
                <div class="text-xs text-gray-500">${task.project_name} - ${task.status}</div>
            </div>
        </label>
    `).join('');
    
    container.innerHTML = html;
}

function updateProjectSelection(projectId, isSelected) {
    if (isSelected) {
        if (!selectedProjects.includes(projectId)) {
            selectedProjects.push(projectId);
        }
    } else {
        selectedProjects = selectedProjects.filter(id => id !== projectId);
    }
}

function updateUserSelection(userId, isSelected) {
    if (isSelected) {
        if (!selectedUsers.includes(userId)) {
            selectedUsers.push(userId);
        }
    } else {
        selectedUsers = selectedUsers.filter(id => id !== userId);
    }
}

function updateTaskSelection(taskId, isSelected) {
    if (isSelected) {
        if (!selectedTasks.includes(taskId)) {
            selectedTasks.push(taskId);
        }
    } else {
        selectedTasks = selectedTasks.filter(id => id !== taskId);
    }
}

function bulkUpdateProjectStatus() {
    const status = document.getElementById('newProjectStatus').value;
    
    if (!status) {
        alert('Please select a status');
        return;
    }
    
    if (selectedProjects.length === 0) {
        alert('Please select at least one project');
        return;
    }
    
    showProgress();
    
    fetch('/admin/project-admin/bulk-update-project-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            project_ids: selectedProjects,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        if (data.success) {
            alert(data.message);
            loadProjects();
            clearProjectSelection();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        alert('An error occurred while updating projects');
    });
}

function bulkAssignUsers() {
    if (selectedUsers.length === 0) {
        alert('Please select at least one user');
        return;
    }
    
    if (selectedProjects.length === 0) {
        alert('Please select at least one project');
        return;
    }
    
    const role = document.getElementById('userRole').value;
    
    showProgress();
    
    fetch('/admin/project-admin/bulk-assign-users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            project_ids: selectedProjects,
            user_ids: selectedUsers,
            role: role
        })
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        if (data.success) {
            alert(data.message);
            clearAllSelections();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        alert('An error occurred while assigning users');
    });
}

function showProgress() {
    document.getElementById('progressContainer').classList.remove('hidden');
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressPercent').textContent = '0%';
    document.getElementById('progressText').textContent = 'Processing...';
}

function hideProgress() {
    document.getElementById('progressContainer').classList.add('hidden');
}

function selectAllProjects() {
    document.querySelectorAll('.project-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        updateProjectSelection(parseInt(checkbox.value), true);
    });
}

function clearProjectSelection() {
    document.querySelectorAll('.project-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    selectedProjects = [];
}

function clearAllSelections() {
    clearProjectSelection();
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    selectedUsers = [];
    selectedTasks = [];
}

// Additional functions would be implemented similarly...
console.log('Bulk Operations Management loaded');
</script>
@endsection