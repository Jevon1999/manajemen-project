<!-- Task Approval Interface Component -->
<!-- Usage: @include('components.task-approval', ['task' => $task]) -->

@props(['task'])

@php
    $canApprove = in_array(auth()->user()->role, ['admin', 'leader']);
    $isPendingApproval = $task->status === 'review' && $task->requires_approval && !$task->approved_at;
    $isApproved = $task->approved_at !== null;
@endphp

<div class="bg-white rounded-lg shadow-md p-6" x-data="taskApproval(@js($task), @js($canApprove))">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-clipboard-check mr-2 text-purple-600"></i>
            Task Approval
        </h3>
        
        <!-- Status Badge -->
        <span class="px-4 py-2 rounded-full text-sm font-semibold"
              :class="{
                  'bg-yellow-100 text-yellow-800': status === 'review',
                  'bg-green-100 text-green-800': status === 'completed',
                  'bg-blue-100 text-blue-800': status === 'in_progress'
              }">
            <i :class="{
                'fas fa-hourglass-half': status === 'review',
                'fas fa-check-circle': status === 'completed',
                'fas fa-tasks': status === 'in_progress'
            }"></i>
            <span x-text="statusText"></span>
        </span>
    </div>

    <!-- Task Info -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="font-semibold text-gray-900 mb-2">{{ $task->card_title }}</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Priority:</span>
                <span class="ml-2 px-2 py-1 rounded text-xs font-semibold"
                      :class="{
                          'bg-red-100 text-red-800': '{{ $task->priority }}' === 'urgent',
                          'bg-orange-100 text-orange-800': '{{ $task->priority }}' === 'high',
                          'bg-yellow-100 text-yellow-800': '{{ $task->priority }}' === 'medium',
                          'bg-gray-100 text-gray-800': '{{ $task->priority }}' === 'low'
                      }">
                    {{ ucfirst($task->priority) }}
                </span>
            </div>
            <div>
                <span class="text-gray-600">Estimated:</span>
                <span class="ml-2 font-semibold">{{ $task->estimated_hours ?? 0 }}h</span>
                <span class="text-gray-400">/</span>
                <span class="ml-1">Actual: <span class="font-semibold" x-text="actualHours + 'h'"></span></span>
            </div>
        </div>
    </div>

    <!-- Approval Status (if approved) -->
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg" x-show="isApproved">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-green-500 text-2xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-green-900 mb-2">Task Approved</h4>
                <div class="text-sm text-green-800">
                    <p>Approved by: <span class="font-semibold" x-text="approver"></span></p>
                    <p>Date: <span x-text="formatApprovedDate()"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approval Notice -->
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg" 
         x-show="isPendingApproval && !canApprove">
        <div class="flex items-start">
            <i class="fas fa-clock text-yellow-500 text-2xl mt-1 mr-3"></i>
            <div>
                <h4 class="text-sm font-semibold text-yellow-900 mb-1">Waiting for Approval</h4>
                <p class="text-sm text-yellow-800">This task is pending review by a leader or admin.</p>
            </div>
        </div>
    </div>

    <!-- Compliance Checks -->
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-tasks mr-2"></i>Completion Checklist
        </h4>
        <div class="space-y-2">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <span class="text-sm text-gray-700">Time logged today</span>
                <span class="px-2 py-1 rounded text-xs font-semibold"
                      :class="hasTimeLogToday ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    <i :class="hasTimeLogToday ? 'fas fa-check' : 'fas fa-times'"></i>
                    <span x-text="hasTimeLogToday ? 'Yes' : 'No'"></span>
                </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <span class="text-sm text-gray-700">Progress comment added</span>
                <span class="px-2 py-1 rounded text-xs font-semibold"
                      :class="hasCommentToday ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    <i :class="hasCommentToday ? 'fas fa-check' : 'fas fa-times'"></i>
                    <span x-text="hasCommentToday ? 'Yes' : 'No'"></span>
                </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <span class="text-sm text-gray-700">All subtasks completed</span>
                <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                    <span x-text="completedSubtasks + '/' + totalSubtasks"></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Approval Actions (for Leader/Admin) -->
    <div class="space-y-3" x-show="canApprove && isPendingApproval">
        <div class="border-t pt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Review Actions</h4>
            
            <!-- Rejection Reason (shown when reject is selected) -->
            <div class="mb-4" x-show="showRejectReason">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Rejection <span class="text-red-500">*</span>
                </label>
                <textarea x-model="rejectionReason"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Explain what needs to be improved..."
                          required></textarea>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button @click="approveTask" 
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50"
                        :disabled="processing"
                        x-show="!showRejectReason">
                    <span x-show="!processing">
                        <i class="fas fa-check mr-2"></i>Approve Task
                    </span>
                    <span x-show="processing">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                    </span>
                </button>
                
                <button @click="showRejectReason = !showRejectReason" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition"
                        :disabled="processing"
                        x-show="!showRejectReason">
                    <i class="fas fa-times mr-2"></i>Reject Task
                </button>
                
                <!-- Confirm Rejection -->
                <template x-if="showRejectReason">
                    <div class="flex-1 flex gap-2">
                        <button @click="rejectTask" 
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50"
                                :disabled="processing || !rejectionReason.trim()">
                            <span x-show="!processing">
                                <i class="fas fa-ban mr-2"></i>Confirm Rejection
                            </span>
                            <span x-show="processing">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                            </span>
                        </button>
                        <button @click="showRejectReason = false; rejectionReason = ''" 
                                class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Permission Notice -->
    <div class="text-center py-4 text-gray-400" x-show="!canApprove && isPendingApproval">
        <i class="fas fa-lock text-2xl mb-2"></i>
        <p class="text-sm">Only leaders and admins can approve tasks</p>
    </div>
</div>

<script>
function taskApproval(task, canApprove) {
    return {
        taskId: task.card_id,
        status: task.status,
        canApprove: canApprove,
        isPendingApproval: task.status === 'review' && task.requires_approval && !task.approved_at,
        isApproved: task.approved_at !== null,
        approver: task.approver ? task.approver.full_name : '',
        approvedAt: task.approved_at,
        hasTimeLogToday: task.has_time_log_today || false,
        hasCommentToday: false,
        actualHours: task.actual_hours || 0,
        completedSubtasks: 0,
        totalSubtasks: 0,
        processing: false,
        showRejectReason: false,
        rejectionReason: '',
        
        get statusText() {
            const statusMap = {
                'in_progress': 'In Progress',
                'review': 'Pending Approval',
                'completed': 'Approved & Completed',
                'blocked': 'Blocked'
            };
            return statusMap[this.status] || this.status;
        },
        
        init() {
            this.loadTaskDetails();
        },
        
        async loadTaskDetails() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/approval-details`);
                const data = await response.json();
                
                if (data.success) {
                    this.hasCommentToday = data.has_comment_today;
                    this.completedSubtasks = data.completed_subtasks || 0;
                    this.totalSubtasks = data.total_subtasks || 0;
                }
            } catch (error) {
                console.error('Error loading task details:', error);
            }
        },
        
        async approveTask() {
            if (!confirm('Are you sure you want to approve this task?')) {
                return;
            }
            
            this.processing = true;
            
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.status = 'completed';
                    this.isApproved = true;
                    this.isPendingApproval = false;
                    this.approver = data.task.approver.full_name;
                    this.approvedAt = data.task.approved_at;
                    
                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: 'Task approved successfully!' 
                    });
                    
                    this.$dispatch('task-approved', { task: data.task });
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to approve task');
                }
            } catch (error) {
                console.error('Error approving task:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: error.message || 'Failed to approve task' 
                });
            } finally {
                this.processing = false;
            }
        },
        
        async rejectTask() {
            if (!this.rejectionReason.trim()) {
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'Please provide a reason for rejection' 
                });
                return;
            }
            
            if (!confirm('Are you sure you want to reject this task?')) {
                return;
            }
            
            this.processing = true;
            
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        reason: this.rejectionReason
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.status = 'in_progress';
                    this.isPendingApproval = false;
                    this.showRejectReason = false;
                    this.rejectionReason = '';
                    
                    this.$dispatch('notify', { 
                        type: 'info', 
                        message: 'Task rejected and sent back for revision' 
                    });
                    
                    this.$dispatch('task-rejected', { task: data.task });
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to reject task');
                }
            } catch (error) {
                console.error('Error rejecting task:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: error.message || 'Failed to reject task' 
                });
            } finally {
                this.processing = false;
            }
        },
        
        formatApprovedDate() {
            if (!this.approvedAt) return '';
            return new Date(this.approvedAt).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
