<!-- Task Detail Modal -->
<div id="task-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="ph-card ph-card-elevated w-full max-w-3xl mx-4 overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="ph-card-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--ph-primary-100);">
                        <svg class="w-5 h-5" style="color: var(--ph-primary-600)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold" style="color: var(--ph-gray-900)">Task Details</h3>
                        <p class="text-sm" style="color: var(--ph-gray-500)">View task information and progress</p>
                    </div>
                </div>
                <button type="button" onclick="closeTaskDetailModal()" class="ph-btn-icon" style="background-color: var(--ph-gray-100); color: var(--ph-gray-600);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="task-loading" class="ph-card-body text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2" style="border-color: var(--ph-primary-500)"></div>
            <p class="mt-3 text-sm" style="color: var(--ph-gray-600)">Loading task details...</p>
        </div>

        <!-- Task Content -->
        <div id="task-content" class="hidden">
            <div class="ph-card-body space-y-6">
                <!-- Task Header -->
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 id="task-title" class="text-xl font-semibold" style="color: var(--ph-gray-900)"></h4>
                        <p id="task-project" class="text-sm mt-1" style="color: var(--ph-gray-600)"></p>
                    </div>
                    <div id="task-status-badge" class="ph-badge"></div>
                </div>

                <!-- Task Description -->
                <div>
                    <h5 class="text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Description</h5>
                    <div id="task-description" class="text-sm p-4 rounded-lg" style="background-color: var(--ph-gray-50); color: var(--ph-gray-600);">
                        No description provided
                    </div>
                </div>

                <!-- Task Info Grid -->
                <div class="grid grid-cols-2 gap-6">
                    <!-- Due Date -->
                    <div>
                        <h5 class="text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Due Date</h5>
                        <div id="task-due-date" class="flex items-center space-x-2">
                            <svg class="w-4 h-4" style="color: var(--ph-gray-500)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm" style="color: var(--ph-gray-600)"></span>
                        </div>
                    </div>

                    <!-- Priority -->
                    <div>
                        <h5 class="text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Priority</h5>
                        <div id="task-priority" class="flex items-center space-x-2">
                            <svg class="w-4 h-4" style="color: var(--ph-gray-500)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                            <span class="text-sm" style="color: var(--ph-gray-600)"></span>
                        </div>
                    </div>
                </div>

                <!-- Assignees -->
                <div>
                    <h5 class="text-sm font-medium mb-2" style="color: var(--ph-gray-700)">Assigned To</h5>
                    <div id="task-assignees" class="flex flex-wrap gap-2">
                        <!-- Assignees will be populated here -->
                    </div>
                </div>

                <!-- Subtasks -->
                <div id="task-subtasks-section" class="hidden">
                    <h5 class="text-sm font-medium mb-3" style="color: var(--ph-gray-700)">Subtasks</h5>
                    <div id="task-subtasks" class="space-y-2">
                        <!-- Subtasks will be populated here -->
                    </div>
                </div>

                <!-- Comments -->
                <div id="task-comments-section" class="hidden">
                    <h5 class="text-sm font-medium mb-3" style="color: var(--ph-gray-700)">Recent Comments</h5>
                    <div id="task-comments" class="space-y-3">
                        <!-- Comments will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="ph-card-header border-t" style="border-color: var(--ph-gray-200);">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-3">
                        <button id="edit-task-btn" class="ph-btn ph-btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Task
                        </button>
                        <button id="mark-complete-btn" class="ph-btn ph-btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mark Complete
                        </button>
                    </div>
                    <button onclick="closeTaskDetailModal()" class="ph-btn ph-btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #task-detail-modal {
        animation: fadeIn 0.3s ease-out;
    }

    #task-detail-modal .ph-card {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { 
            opacity: 0; 
            transform: translateY(-30px) scale(0.98); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0) scale(1); 
        }
    }

    .assignee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        color: white;
        background: linear-gradient(135deg, var(--ph-primary-500), var(--ph-primary-600));
    }

    .subtask-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: var(--ph-radius-md);
        background-color: var(--ph-gray-50);
        border: 1px solid var(--ph-gray-200);
        transition: all 0.2s ease-in-out;
    }

    .subtask-item:hover {
        background-color: var(--ph-gray-100);
    }

    .comment-item {
        padding: 12px;
        border-radius: var(--ph-radius-md);
        background-color: var(--ph-gray-50);
        border-left: 3px solid var(--ph-primary-500);
    }
</style>

<script>
    async function viewTaskDetails(taskId) {
        const modal = document.getElementById('task-detail-modal');
        const loading = document.getElementById('task-loading');
        const content = document.getElementById('task-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        try {
            const response = await fetch(`/api/tasks/${taskId}/details`);
            if (!response.ok) throw new Error('Failed to fetch task details');
            
            const task = await response.json();
            populateTaskDetails(task);
            
            // Show content and hide loading
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching task details:', error);
            
            // Show error message
            loading.innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--ph-error)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm" style="color: var(--ph-error)">Failed to load task details</p>
                    <button onclick="closeTaskDetailModal()" class="ph-btn ph-btn-secondary mt-3">Close</button>
                </div>
            `;
        }
    }

    function populateTaskDetails(task) {
        // Basic task info
        document.getElementById('task-title').textContent = task.title;
        document.getElementById('task-project').textContent = task.project_name || 'Unknown Project';
        
        // Status badge
        const statusBadge = document.getElementById('task-status-badge');
        statusBadge.textContent = task.status?.toUpperCase() || 'PENDING';
        statusBadge.className = `ph-badge ${getStatusBadgeClass(task.status)}`;
        
        // Description
        const descElement = document.getElementById('task-description');
        descElement.textContent = task.description || 'No description provided';
        
        // Due date
        const dueDateElement = document.getElementById('task-due-date');
        const dueDateSpan = dueDateElement.querySelector('span');
        if (task.due_date) {
            dueDateSpan.textContent = new Date(task.due_date).toLocaleDateString();
            if (new Date(task.due_date) < new Date()) {
                dueDateSpan.style.color = 'var(--ph-error)';
                dueDateSpan.textContent += ' (Overdue)';
            }
        } else {
            dueDateSpan.textContent = 'No due date set';
        }
        
        // Priority
        const priorityElement = document.getElementById('task-priority');
        const prioritySpan = priorityElement.querySelector('span');
        prioritySpan.textContent = task.priority || 'Normal';
        
        // Assignees
        const assigneesContainer = document.getElementById('task-assignees');
        assigneesContainer.innerHTML = '';
        
        if (task.assignees && task.assignees.length > 0) {
            task.assignees.forEach(assignee => {
                const assigneeEl = document.createElement('div');
                assigneeEl.className = 'flex items-center space-x-2 bg-gray-100 rounded-full px-3 py-1';
                assigneeEl.innerHTML = `
                    <div class="assignee-avatar">
                        ${assignee.full_name.charAt(0).toUpperCase()}
                    </div>
                    <span class="text-sm" style="color: var(--ph-gray-700)">${assignee.full_name}</span>
                `;
                assigneesContainer.appendChild(assigneeEl);
            });
        } else {
            assigneesContainer.innerHTML = '<span class="text-sm" style="color: var(--ph-gray-500)">No assignees</span>';
        }
        
        // Subtasks
        const subtasksSection = document.getElementById('task-subtasks-section');
        const subtasksContainer = document.getElementById('task-subtasks');
        
        if (task.subtasks && task.subtasks.length > 0) {
            subtasksSection.classList.remove('hidden');
            subtasksContainer.innerHTML = '';
            
            task.subtasks.forEach(subtask => {
                const subtaskEl = document.createElement('div');
                subtaskEl.className = 'subtask-item';
                subtaskEl.innerHTML = `
                    <input type="checkbox" ${subtask.completed ? 'checked' : ''} disabled class="mr-3">
                    <span class="text-sm ${subtask.completed ? 'line-through text-gray-500' : ''}" style="color: var(--ph-gray-700)">
                        ${subtask.title}
                    </span>
                `;
                subtasksContainer.appendChild(subtaskEl);
            });
        } else {
            subtasksSection.classList.add('hidden');
        }
        
        // Comments
        const commentsSection = document.getElementById('task-comments-section');
        const commentsContainer = document.getElementById('task-comments');
        
        if (task.comments && task.comments.length > 0) {
            commentsSection.classList.remove('hidden');
            commentsContainer.innerHTML = '';
            
            task.comments.slice(0, 3).forEach(comment => {
                const commentEl = document.createElement('div');
                commentEl.className = 'comment-item';
                commentEl.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="assignee-avatar">
                            ${comment.user_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="text-sm font-medium" style="color: var(--ph-gray-900)">${comment.user_name}</span>
                                <span class="text-xs" style="color: var(--ph-gray-500)">${new Date(comment.created_at).toLocaleDateString()}</span>
                            </div>
                            <p class="text-sm" style="color: var(--ph-gray-600)">${comment.content}</p>
                        </div>
                    </div>
                `;
                commentsContainer.appendChild(commentEl);
            });
        } else {
            commentsSection.classList.add('hidden');
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'completed': return 'ph-badge-success';
            case 'in_progress': return 'ph-badge-info';
            case 'pending': return 'ph-badge-warning';
            default: return 'ph-badge-warning';
        }
    }

    function closeTaskDetailModal() {
        const modal = document.getElementById('task-detail-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeTaskDetailModal();
    });

    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('task-detail-modal');
        if (e.target === modal) {
            closeTaskDetailModal();
        }
    });
</script>