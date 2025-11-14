/**
 * Work Session Timer - Frontend Timer Management
 * Handles start/stop work tracking with localStorage persistence
 */

class WorkTimer {
    constructor() {
        this.timerInterval = null;
        this.startTime = null;
        this.elapsedSeconds = 0;
        this.sessionId = null;
        this.taskId = null;
        this.isPaused = false;
        this.pausedAt = null;
        
        // Load from localStorage if exists
        this.loadFromStorage();
        
        // Resume timer if there's an active session
        if (this.sessionId) {
            this.resumeTimer();
        }
    }
    
    /**
     * Start a new work session
     */
    async startWork(taskId = null) {
        try {
            // Check if already running
            if (this.timerInterval) {
                this.showNotification('Timer is already running', 'warning');
                return false;
            }
            
            // Check daily limit first
            const todayTotal = await this.getTodayTotal();
            if (todayTotal.limit_reached) {
                this.showNotification('You have reached the daily work limit of 8 hours', 'error');
                return false;
            }
            
            // Start session on backend
            const response = await fetch('/work-sessions/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to start work session', 'error');
                return false;
            }
            
            // Initialize timer
            this.sessionId = data.session.session_id;
            this.taskId = taskId;
            this.startTime = Date.now();
            this.elapsedSeconds = 0;
            
            // Save to localStorage
            this.saveToStorage();
            
            // Start counting
            this.startCounting();
            
            // Update UI
            this.updateUI();
            
            this.showNotification('Work session started!', 'success');
            return true;
            
        } catch (error) {
            console.error('Error starting work:', error);
            this.showNotification('Failed to start work session', 'error');
            return false;
        }
    }
    
    /**
     * Stop current work session (PERMANENT STOP)
     */
    async stopWork() {
        try {
            if (!this.sessionId) {
                this.showNotification('No active work session', 'warning');
                return false;
            }
            
            // Stop counting
            this.stopCounting();
            
            // Send to backend
            const response = await fetch('/work-sessions/stop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    duration_seconds: this.elapsedSeconds,
                    action: 'stop' // PERMANENT STOP
                })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to stop work session', 'error');
                
                // If session is stale, reset
                if (data.stale_session || data.action === 'reset') {
                    this.reset();
                    this.updateUI();
                } else {
                    // Restart timer since stop failed
                    this.startCounting();
                }
                return false;
            }
            
            // Clear session
            this.reset();
            
            // Update UI
            this.updateUI();
            
            this.showNotification(`Work session completed! Duration: ${data.formatted_duration}`, 'success');
            return true;
            
        } catch (error) {
            console.error('Error stopping work:', error);
            this.showNotification('Failed to stop work session', 'error');
            // Restart timer since stop failed
            this.startCounting();
            return false;
        }
    }
    
    /**
     * Pause current work session (TEMPORARY PAUSE)
     */
    async pauseWork() {
        try {
            if (!this.sessionId) {
                this.showNotification('No active work session', 'warning');
                return false;
            }
            
            // Stop counting
            this.stopCounting();
            
            // Send to backend
            const response = await fetch('/work-sessions/stop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    duration_seconds: this.elapsedSeconds,
                    action: 'pause' // TEMPORARY PAUSE
                })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to pause work session', 'error');
                
                // If session is stale, reset
                if (data.stale_session || data.action === 'reset') {
                    this.reset();
                    this.updateUI();
                } else {
                    // Restart timer since pause failed
                    this.startCounting();
                }
                return false;
            }
            
            // Mark as paused (keep session data)
            this.isPaused = true;
            this.pausedAt = Date.now();
            this.saveToStorage();
            
            // Update UI
            this.updateUI();
            
            this.showNotification('Work session paused', 'info');
            return true;
            
        } catch (error) {
            console.error('Error pausing work:', error);
            this.showNotification('Failed to pause work session', 'error');
            // Restart timer since pause failed
            this.startCounting();
            return false;
        }
    }
    
    /**
     * Resume paused work session
     */
    async resumePausedWork() {
        try {
            if (!this.sessionId || !this.isPaused) {
                this.showNotification('No paused session found', 'warning');
                return false;
            }
            
            // Resume on backend
            const response = await fetch('/work-sessions/resume', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to resume work session', 'error');
                
                // If session is stale, reset
                if (data.stale_session || data.action === 'reset') {
                    this.reset();
                    this.updateUI();
                }
                return false;
            }
            
            // Resume counting
            this.isPaused = false;
            this.pausedAt = null;
            this.startCounting();
            this.saveToStorage();
            
            // Update UI
            this.updateUI();
            
            this.showNotification('Work session resumed!', 'success');
            return true;
            
        } catch (error) {
            console.error('Error resuming work:', error);
            this.showNotification('Failed to resume work session', 'error');
            return false;
        }
    }
    
    /**
     * Start counting timer
     */
    startCounting() {
        // IMPORTANT: Stop any existing interval first to prevent duplicate timers
        this.stopCounting();
        
        this.timerInterval = setInterval(() => {
            this.elapsedSeconds++;
            this.saveToStorage();
            this.updateTimerDisplay();
            
            // Check if reached 8 hours
            if (this.elapsedSeconds >= 28800) {
                this.stopWork();
                this.showNotification('Daily work limit reached (8 hours)', 'warning');
            }
        }, 1000);
    }
    
    /**
     * Stop counting timer (CRITICAL: Clear interval properly)
     */
    stopCounting() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null; // IMPORTANT: Set to null to prevent resume issues
        }
    }
    
    /**
     * Resume timer from localStorage
     */
    resumeTimer() {
        if (this.sessionId && this.startTime) {
            // Calculate elapsed time since start (not paused time)
            const now = Date.now();
            const totalElapsed = Math.floor((now - this.startTime) / 1000);
            
            // Only update if timer should be running
            // Check if session is still active on server
            this.verifySessionAndResume(totalElapsed);
        }
    }
    
    /**
     * Verify session is still active before resuming
     */
    async verifySessionAndResume(totalElapsed) {
        try {
            const response = await fetch('/work-sessions/active', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            // Check for stale session alert from backend
            if (data.stale_session_cancelled) {
                this.showNotification(data.message || 'Previous session was cancelled due to inactivity', 'warning');
                this.reset();
                this.updateUI();
                return;
            }
            
            if (data.success && data.session && data.session.session_id === this.sessionId) {
                // Check session age (24 hours = 86400 seconds)
                if (totalElapsed >= 86400) {
                    // Session is too old (> 24 hours), auto-cancel
                    this.showNotification('Your previous session expired (> 24 hours). Please start a new session.', 'warning');
                    this.reset();
                    this.updateUI();
                    return;
                }
                
                // Check if session is paused
                if (data.session.paused_at) {
                    this.isPaused = true;
                    this.pausedAt = new Date(data.session.paused_at).getTime();
                    this.elapsedSeconds = data.session.duration_seconds;
                    this.saveToStorage();
                    this.updateUI();
                } else {
                    // Session is active on server, resume timer
                    this.elapsedSeconds = totalElapsed;
                    this.isPaused = false;
                    this.pausedAt = null;
                    this.startCounting();
                    this.updateUI();
                }
            } else {
                // Session not active on server, reset local timer
                this.reset();
                this.updateUI();
            }
        } catch (error) {
            console.error('Error verifying session:', error);
            // On error, reset to be safe
            this.reset();
            this.updateUI();
        }
    }
    
    /**
     * Reset timer state
     */
    reset() {
        this.stopCounting();
        this.sessionId = null;
        this.taskId = null;
        this.startTime = null;
        this.elapsedSeconds = 0;
        this.isPaused = false;
        this.pausedAt = null;
        localStorage.removeItem('workTimer');
    }
    
    /**
     * Save to localStorage
     */
    saveToStorage() {
        const state = {
            sessionId: this.sessionId,
            taskId: this.taskId,
            startTime: this.startTime,
            elapsedSeconds: this.elapsedSeconds,
            isPaused: this.isPaused,
            pausedAt: this.pausedAt
        };
        localStorage.setItem('workTimer', JSON.stringify(state));
    }
    
    /**
     * Load from localStorage
     */
    loadFromStorage() {
        const stored = localStorage.getItem('workTimer');
        if (stored) {
            try {
                const state = JSON.parse(stored);
                this.sessionId = state.sessionId;
                this.taskId = state.taskId;
                this.startTime = state.startTime;
                this.elapsedSeconds = state.elapsedSeconds || 0;
                this.isPaused = state.isPaused || false;
                this.pausedAt = state.pausedAt || null;
            } catch (error) {
                console.error('Error loading timer from storage:', error);
                localStorage.removeItem('workTimer');
            }
        }
    }
    
    /**
     * Format seconds to HH:MM:SS
     */
    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    
    /**
     * Update timer display in UI
     */
    updateTimerDisplay() {
        const display = document.getElementById('timer-display');
        if (display) {
            display.textContent = this.formatTime(this.elapsedSeconds);
        }
    }
    
    /**
     * Update UI buttons and display
     */
    updateUI() {
        const startBtn = document.getElementById('start-work-btn');
        const stopBtn = document.getElementById('stop-work-btn');
        const pauseBtn = document.getElementById('pause-work-btn');
        const resumeBtn = document.getElementById('resume-work-btn');
        const timerContainer = document.getElementById('timer-container');
        
        if (this.sessionId) {
            if (this.isPaused) {
                // Session is paused
                if (startBtn) startBtn.style.display = 'none';
                if (stopBtn) stopBtn.style.display = 'inline-flex';
                if (pauseBtn) pauseBtn.style.display = 'none';
                if (resumeBtn) resumeBtn.style.display = 'inline-flex';
                if (timerContainer) timerContainer.style.display = 'block';
                this.updateTimerDisplay();
            } else if (this.timerInterval) {
                // Timer is running
                if (startBtn) startBtn.style.display = 'none';
                if (stopBtn) stopBtn.style.display = 'inline-flex';
                if (pauseBtn) pauseBtn.style.display = 'inline-flex';
                if (resumeBtn) resumeBtn.style.display = 'none';
                if (timerContainer) timerContainer.style.display = 'block';
                this.updateTimerDisplay();
            }
        } else {
            // Timer is not running
            if (startBtn) startBtn.style.display = 'inline-flex';
            if (stopBtn) stopBtn.style.display = 'none';
            if (pauseBtn) pauseBtn.style.display = 'none';
            if (resumeBtn) resumeBtn.style.display = 'none';
            if (timerContainer) timerContainer.style.display = 'none';
        }
        
        // Update today's total
        this.updateTodayTotal();
    }
    
    /**
     * Get today's total work time
     */
    async getTodayTotal() {
        try {
            const response = await fetch('/work-sessions/today-total', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                return data;
            }
            return { today_total: 0, formatted: '00:00:00', limit_reached: false };
        } catch (error) {
            console.error('Error getting today total:', error);
            return { today_total: 0, formatted: '00:00:00', limit_reached: false };
        }
    }
    
    /**
     * Update today's total display
     */
    async updateTodayTotal() {
        const todayDisplay = document.getElementById('today-total-display');
        const remainingDisplay = document.getElementById('remaining-time-display');
        
        if (todayDisplay || remainingDisplay) {
            const data = await this.getTodayTotal();
            if (todayDisplay) {
                todayDisplay.textContent = data.formatted;
            }
            if (remainingDisplay) {
                remainingDisplay.textContent = data.formatted_remaining;
                
                // Change color based on remaining time
                if (data.remaining_seconds < 3600) { // Less than 1 hour
                    remainingDisplay.classList.add('text-red-600');
                    remainingDisplay.classList.remove('text-green-600');
                } else {
                    remainingDisplay.classList.add('text-green-600');
                    remainingDisplay.classList.remove('text-red-600');
                }
            }
        }
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        // Add to document
        document.body.appendChild(notification);
        
        // Slide in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    /**
     * Check for active session on page load
     */
    async checkActiveSession() {
        try {
            const response = await fetch('/work-sessions/active', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.session) {
                // Found active session on server
                this.sessionId = data.session.session_id;
                this.taskId = data.session.task_id;
                this.startTime = new Date(data.session.started_at).getTime();
                
                // Calculate elapsed time from server start time
                const now = Date.now();
                this.elapsedSeconds = Math.floor((now - this.startTime) / 1000);
                
                // Save and resume
                this.saveToStorage();
                this.resumeTimer();
            }
        } catch (error) {
            console.error('Error checking active session:', error);
        }
    }
}

// Initialize timer when DOM is ready
let workTimer;

document.addEventListener('DOMContentLoaded', function() {
    workTimer = new WorkTimer();
    
    // Check for active session
    workTimer.checkActiveSession();
    
    // Update UI initially
    workTimer.updateUI();
    
    // Bind events to buttons
    const startBtn = document.getElementById('start-work-btn');
    const stopBtn = document.getElementById('stop-work-btn');
    const pauseBtn = document.getElementById('pause-work-btn');
    const resumeBtn = document.getElementById('resume-work-btn');
    
    if (startBtn) {
        startBtn.addEventListener('click', async function() {
            // Get user's active task first
            try {
                const taskResponse = await fetch('/api/my-active-task', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const taskData = await taskResponse.json();
                
                if (taskData.success && taskData.task_id) {
                    // Start work with the user's task
                    const started = await workTimer.startWork(taskData.task_id);
                    if (started) {
                        // Update UI without full page reload
                        workTimer.updateUI();
                    }
                } else {
                    workTimer.showNotification(taskData.message || 'Tidak ada task yang tersedia', 'error');
                }
            } catch (error) {
                console.error('Error getting active task:', error);
                workTimer.showNotification('Gagal mendapatkan task', 'error');
            }
        });
    }
    
    if (stopBtn) {
        stopBtn.addEventListener('click', async function() {
            const stopped = await workTimer.stopWork();
            if (stopped) {
                // Update UI without full page reload
                workTimer.updateUI();
            }
        });
    }
    
    if (pauseBtn) {
        pauseBtn.addEventListener('click', async function() {
            const paused = await workTimer.pauseWork();
            if (paused) {
                // Update UI without full page reload
                workTimer.updateUI();
            }
        });
    }
    
    if (resumeBtn) {
        resumeBtn.addEventListener('click', async function() {
            const resumed = await workTimer.resumePausedWork();
            if (resumed) {
                // Update UI without full page reload
                workTimer.updateUI();
            }
        });
    }
});

// Make workTimer available globally
window.workTimer = workTimer;
