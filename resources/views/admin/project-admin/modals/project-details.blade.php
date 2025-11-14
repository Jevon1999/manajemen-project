<!-- Project Details Modal -->
<div id="projectDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="projectDetailsTitle">Project Details</h3>
                        <button onclick="hideProjectDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4" id="projectDetailsContent">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-gray-500">Loading project details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showProjectDetails(projectId) {
    document.getElementById('projectDetailsModal').classList.remove('hidden');
    loadProjectDetails(projectId);
}

function hideProjectDetailsModal() {
    document.getElementById('projectDetailsModal').classList.add('hidden');
}

function loadProjectDetails(projectId) {
    fetch(`/admin/project-admin/analytics/${projectId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayProjectDetails(data.project, data.analytics);
        } else {
            document.getElementById('projectDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600">Failed to load project details</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('projectDetailsContent').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-600">An error occurred while loading project details</p>
            </div>
        `;
    });
}

function displayProjectDetails(project, analytics) {
    document.getElementById('projectDetailsTitle').textContent = project.project_name;
    
    const statusColors = {
        'planning': 'bg-yellow-100 text-yellow-800',
        'active': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800',
        'on-hold': 'bg-red-100 text-red-800'
    };
    
    const content = `
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Project Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Project Information</h4>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[project.status] || 'bg-gray-100 text-gray-800'}">
                                    ${project.status.charAt(0).toUpperCase() + project.status.slice(1)}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1 text-sm text-gray-900">${project.priority ? project.priority.charAt(0).toUpperCase() + project.priority.slice(1) : 'Not set'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">${project.start_date ? new Date(project.start_date).toLocaleDateString() : 'Not set'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">${project.end_date ? new Date(project.end_date).toLocaleDateString() : 'Not set'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Budget</dt>
                            <dd class="mt-1 text-sm text-gray-900">${project.budget ? '$' + parseFloat(project.budget).toLocaleString() : 'Not set'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">${project.category || 'Not specified'}</dd>
                        </div>
                    </dl>
                </div>
                
                ${project.description ? `
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Description</h4>
                    <p class="text-sm text-gray-700">${project.description}</p>
                </div>` : ''}
                
                <!-- Team Members -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Team Members (${project.members ? project.members.length : 0})</h4>
                    ${project.members && project.members.length > 0 ? `
                        <div class="space-y-2">
                            ${project.members.map(member => `
                                <div class="flex items-center justify-between py-2 px-3 bg-white rounded border">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700">
                                            ${member.user.full_name.charAt(0)}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">${member.user.full_name}</p>
                                            <p class="text-xs text-gray-500">${member.user.email}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        ${member.role}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p class="text-sm text-gray-500">No team members assigned</p>'}
                </div>
            </div>
            
            <!-- Analytics -->
            <div class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Project Analytics</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700">Completion Rate</span>
                                <span class="text-gray-900">${analytics.completion_rate || 0}%</span>
                            </div>
                            <div class="mt-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${analytics.completion_rate || 0}%"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Total Hours</span>
                            <span class="text-sm text-gray-900">${Math.round(analytics.total_hours || 0)}h</span>
                        </div>
                        
                        ${analytics.timeline_progress ? `
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700">Timeline Progress</span>
                                <span class="text-gray-900">${analytics.timeline_progress.time_progress}%</span>
                            </div>
                            <div class="mt-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: ${analytics.timeline_progress.time_progress}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                ${analytics.timeline_progress.days_remaining} days remaining
                                ${analytics.timeline_progress.is_on_track ? '✅ On Track' : '⚠️ Behind Schedule'}
                            </p>
                        </div>` : ''}
                    </div>
                </div>
                
                ${analytics.task_distribution ? `
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Task Distribution</h4>
                    <div class="space-y-2">
                        ${analytics.task_distribution.map(task => `
                            <div class="flex items-center justify-between text-sm">
                                <span class="capitalize text-gray-700">${task.status.replace('_', ' ')}</span>
                                <span class="font-medium text-gray-900">${task.count}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('projectDetailsContent').innerHTML = content;
}
</script>