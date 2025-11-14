<!-- Time Tracking Widget Component -->
<!-- Usage: @include('components.time-tracker', ['task' => $task]) -->

@props(['task'])

<div class="bg-white rounded-lg shadow-md p-6" x-data="timeTracker(@js($task))">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-clock mr-2 text-indigo-600"></i>
            Time Tracking
        </h3>
        <span class="text-sm text-gray-500">Task #{{ $task->card_id }}</span>
    </div>

    <!-- Active Timer Display -->
    <div class="mb-6" x-show="isTimerRunning">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Time Elapsed</p>
                    <p class="text-3xl font-bold" x-text="formatElapsedTime()"></p>
                </div>
                <button @click="stopTimer" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-stop mr-2"></i>
                    Stop Timer
                </button>
            </div>
        </div>
    </div>

    <!-- Start Timer Button -->
    <div class="mb-6" x-show="!isTimerRunning && !showManualEntry">
        <button @click="startTimer" 
                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition">
            <i class="fas fa-play mr-2"></i>
            Start Working
        </button>
        <button @click="showManualEntry = true" 
                class="w-full mt-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 rounded-lg transition">
            <i class="fas fa-keyboard mr-2"></i>
            Log Time Manually
        </button>
    </div>

    <!-- Manual Time Entry Form -->
    <div class="mb-6" x-show="showManualEntry && !isTimerRunning">
        <form @submit.prevent="submitManualTime">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hours Worked</label>
                <input type="number" 
                       step="0.5" 
                       min="0.5" 
                       max="24"
                       x-model="manualHours"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="e.g., 2.5"
                       required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                <textarea x-model="manualDescription"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="What did you work on?"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" 
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition"
                        :disabled="submitting">
                    <span x-show="!submitting">
                        <i class="fas fa-save mr-2"></i>Log Time
                    </span>
                    <span x-show="submitting">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
                    </span>
                </button>
                <button type="button" 
                        @click="showManualEntry = false"
                        class="px-4 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Today's Summary -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Today's Summary</h4>
        
        <!-- Has Time Log Badge -->
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-600">Time Logged Today</span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="hasTimeLogToday ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                <i :class="hasTimeLogToday ? 'fas fa-check' : 'fas fa-times'"></i>
                <span x-text="hasTimeLogToday ? 'Yes' : 'No'"></span>
            </span>
        </div>

        <!-- Total Hours Today -->
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-600">Total Hours</span>
            <span class="text-lg font-bold text-indigo-600" x-text="todayHours + ' hrs'"></span>
        </div>

        <!-- Time Logs List -->
        <div class="space-y-2 mt-4" x-show="timeLogs.length > 0">
            <template x-for="log in timeLogs" :key="log.id">
                <div class="bg-gray-50 rounded p-3 text-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-900" x-text="log.hours + ' hours'"></span>
                        <span class="text-xs text-gray-500" x-text="formatTime(log.created_at)"></span>
                    </div>
                    <p class="text-gray-600 text-xs" x-text="log.description || 'No description'"></p>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div class="text-center py-4 text-gray-400" x-show="timeLogs.length === 0">
            <i class="fas fa-clock text-3xl mb-2"></i>
            <p class="text-sm">No time logged today</p>
        </div>
    </div>

    <!-- Total Task Hours -->
    <div class="border-t mt-4 pt-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Estimated</p>
                <p class="text-xl font-bold text-gray-700">{{ $task->estimated_hours ?? 0 }}h</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">Actual</p>
                <p class="text-xl font-bold" 
                   :class="actualHours > ({{ $task->estimated_hours ?? 0 }}) ? 'text-red-600' : 'text-green-600'"
                   x-text="actualHours + 'h'"></p>
            </div>
        </div>
    </div>
</div>

<script>
function timeTracker(task) {
    return {
        taskId: task.card_id,
        isTimerRunning: false,
        timerStartTime: null,
        elapsedSeconds: 0,
        timerInterval: null,
        showManualEntry: false,
        manualHours: '',
        manualDescription: '',
        submitting: false,
        hasTimeLogToday: task.has_time_log_today || false,
        todayHours: 0,
        actualHours: task.actual_hours || 0,
        timeLogs: [],
        
        init() {
            this.loadTodayTimeLogs();
            this.checkActiveTimer();
        },
        
        async checkActiveTimer() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/active-timer`);
                const data = await response.json();
                
                if (data.active_timer) {
                    this.isTimerRunning = true;
                    this.timerStartTime = new Date(data.active_timer.start_time);
                    this.startTimerInterval();
                }
            } catch (error) {
                console.error('Error checking active timer:', error);
            }
        },
        
        async loadTodayTimeLogs() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/time-logs/today`);
                const data = await response.json();
                
                this.timeLogs = data.logs || [];
                this.todayHours = data.total_hours || 0;
                this.hasTimeLogToday = this.todayHours > 0;
            } catch (error) {
                console.error('Error loading time logs:', error);
            }
        },
        
        async startTimer() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/timer/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isTimerRunning = true;
                    this.timerStartTime = new Date();
                    this.elapsedSeconds = 0;
                    this.startTimerInterval();
                    
                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: 'Timer started!' 
                    });
                }
            } catch (error) {
                console.error('Error starting timer:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'Failed to start timer' 
                });
            }
        },
        
        async stopTimer() {
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/timer/stop`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isTimerRunning = false;
                    clearInterval(this.timerInterval);
                    this.elapsedSeconds = 0;
                    
                    await this.loadTodayTimeLogs();
                    this.actualHours = data.task.actual_hours;
                    
                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: `Time logged: ${data.hours_logged} hours` 
                    });
                }
            } catch (error) {
                console.error('Error stopping timer:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'Failed to stop timer' 
                });
            }
        },
        
        async submitManualTime() {
            if (!this.manualHours || this.manualHours <= 0) {
                return;
            }
            
            this.submitting = true;
            
            try {
                const response = await fetch(`/api/tasks/${this.taskId}/time-logs`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        hours: this.manualHours,
                        description: this.manualDescription
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showManualEntry = false;
                    this.manualHours = '';
                    this.manualDescription = '';
                    
                    await this.loadTodayTimeLogs();
                    this.actualHours = data.task.actual_hours;
                    
                    this.$dispatch('notify', { 
                        type: 'success', 
                        message: 'Time logged successfully!' 
                    });
                }
            } catch (error) {
                console.error('Error logging time:', error);
                this.$dispatch('notify', { 
                    type: 'error', 
                    message: 'Failed to log time' 
                });
            } finally {
                this.submitting = false;
            }
        },
        
        startTimerInterval() {
            this.timerInterval = setInterval(() => {
                this.elapsedSeconds++;
            }, 1000);
        },
        
        formatElapsedTime() {
            const totalSeconds = this.elapsedSeconds;
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },
        
        formatTime(datetime) {
            return new Date(datetime).toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    }
}
</script>
