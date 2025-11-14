<!-- Daily Progress Comment Form Component -->
<!-- Usage: @include('components.progress-comment-form', ['task' => $task]) -->

@props(['task'])

<div class="bg-white rounded-lg shadow-md p-6" x-data="progressComment(@js($task))">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-comment-dots mr-2 text-blue-600"></i>
            Daily Progress Update
        </h3>
        <!-- Compliance Badge -->
        <span class="px-3 py-1 rounded-full text-xs font-semibold"
              :class="needsUpdate ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'">
            <i :class="needsUpdate ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle'"></i>
            <span x-text="needsUpdate ? 'Update Required' : 'Up to Date'"></span>
        </span>
    </div>

    <!-- Warning if no time log -->
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4" x-show="!hasTimeLogToday">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
            <div>
                <h4 class="text-sm font-semibold text-red-800 mb-1">Time Tracking Required</h4>
                <p class="text-sm text-red-700">You must log time before adding progress updates.</p>
            </div>
        </div>
    </div>

    <!-- Progress Form -->
    <form @submit.prevent="submitProgress" x-show="hasTimeLogToday">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                What did you accomplish today? <span class="text-red-500">*</span>
            </label>
            <textarea x-model="comment"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Describe your progress, challenges faced, and next steps..."
                      required
                      :disabled="!hasTimeLogToday"></textarea>
            <p class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle"></i>
                Be specific about what you completed and any blockers you encountered.
            </p>
        </div>

        <!-- Quick Status Update -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Task Status</label>
            <select x-model="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :disabled="!hasTimeLogToday">
                <option value="in_progress">In Progress</option>
                <option value="blocked">Blocked</option>
                <option value="review">Ready for Review</option>
            </select>
        </div>

        <!-- Completion Percentage (Optional) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Completion Percentage (Optional)
            </label>
            <div class="flex items-center gap-4">
                <input type="range" 
                       x-model="completionPercentage"
                       min="0" 
                       max="100" 
                       step="5"
                       class="flex-1"
                       :disabled="!hasTimeLogToday">
                <span class="text-lg font-bold text-indigo-600 w-16 text-right" 
                      x-text="completionPercentage + '%'"></span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-2">
            <button type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="submitting || !hasTimeLogToday">
                <span x-show="!submitting">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Progress Update
                </span>
                <span x-show="submitting">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Submitting...
                </span>
            </button>
        </div>
    </form>

    <!-- Today's Comments -->
    <div class="border-t mt-6 pt-6" x-show="comments.length > 0">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-history mr-2"></i>Previous Updates Today
        </h4>
        <div class="space-y-3">
            <template x-for="comment in comments" :key="comment.id">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <img :src="comment.user.avatar || '/images/default-avatar.png'" 
                                 :alt="comment.user.name"
                                 class="w-8 h-8 rounded-full">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="comment.user.full_name"></p>
                                <p class="text-xs text-gray-500" x-text="formatTime(comment.created_at)"></p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"
                              x-text="comment.type || 'Progress Update'"></span>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="comment.comment_text"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- Empty State -->
    <div class="border-t mt-6 pt-6 text-center text-gray-400" x-show="comments.length === 0 && hasTimeLogToday">
        <i class="fas fa-comments text-4xl mb-2"></i>
        <p class="text-sm">No progress updates yet today</p>
    </div>

    <!-- Last Update Info -->
    <div class="border-t mt-4 pt-4 text-sm text-gray-600" x-show="lastUpdate">
        <i class="fas fa-clock mr-2"></i>
        Last updated: <span x-text="formatLastUpdate()" class="font-semibold"></span>
    </div>
</div>

<script>
function progressComment(task) {
    return {
        taskId: task.card_id,
        comment: '',
        status: task.status || 'in_progress',
        completionPercentage: 0,
        submitting: false,
        hasTimeLogToday: task.has_time_log_today || false,
        needsUpdate: task.needs_daily_update || false,
        lastUpdate: task.last_progress_update || null,
        comments: [],
        
        init() {
            this.loadTodayComments();
            this.checkTimeLogStatus();
            
            // Listen for time log events
            window.addEventListener('time-logged', () => {
                this.hasTimeLogToday = true;
            });
        },
        
        async checkTimeLogStatus() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/time-log-status`);
                const data = await response.json();
                this.hasTimeLogToday = data.has_time_log_today;
            } catch (error) {
                console.error('Error checking time log status:', error);
            }
        },
        
        async loadTodayComments() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/comments/today`);
                const data = await response.json();
                this.comments = data.comments || [];
            } catch (error) {
                console.error('Error loading comments:', error);
            }
        },
        
        async submitProgress() {
            if (!this.comment.trim()) {
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'Please enter a progress update' 
                });
                return;
            }
            
            if (!this.hasTimeLogToday) {
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'You must log time before adding progress updates' 
                });
                return;
            }
            
            this.submitting = true;
            
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/progress`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        comment: this.comment,
                        status: this.status,
                        completion_percentage: this.completionPercentage
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.comment = '';
                    this.needsUpdate = false;
                    this.lastUpdate = new Date().toISOString();
                    
                    await this.loadTodayComments();
                    
                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: 'Progress update submitted successfully!' 
                    });
                    
                    this.$dispatch('progress-updated', { task: data.task });
                } else {
                    throw new Error(data.message || 'Failed to submit progress');
                }
            } catch (error) {
                console.error('Error submitting progress:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: error.message || 'Failed to submit progress update' 
                });
            } finally {
                this.submitting = false;
            }
        },
        
        formatTime(datetime) {
            return new Date(datetime).toLocaleString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true
            });
        },
        
        formatLastUpdate() {
            if (!this.lastUpdate) return 'Never';
            
            const date = new Date(this.lastUpdate);
            const now = new Date();
            const diffHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffHours < 1) {
                const diffMinutes = Math.floor((now - date) / (1000 * 60));
                return `${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`;
            } else if (diffHours < 24) {
                return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString();
            }
        }
    }
}
</script>
