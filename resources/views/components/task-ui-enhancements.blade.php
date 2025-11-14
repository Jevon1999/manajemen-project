<!-- Loading Skeleton Component -->
<div class="task-skeleton animate-pulse" id="task-loading">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <!-- Title skeleton -->
                <div class="h-6 bg-gray-200 rounded w-3/4 mb-2"></div>
                <!-- Badges skeleton -->
                <div class="flex space-x-2 mb-3">
                    <div class="h-5 bg-gray-200 rounded-full w-16"></div>
                    <div class="h-5 bg-gray-200 rounded-full w-20"></div>
                </div>
                <!-- Description skeleton -->
                <div class="space-y-2 mb-3">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                </div>
                <!-- Meta info skeleton -->
                <div class="flex space-x-4">
                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                </div>
            </div>
            <!-- Action buttons skeleton -->
            <div class="ml-4 flex space-x-2">
                <div class="h-8 bg-gray-200 rounded w-24"></div>
                <div class="h-8 bg-gray-200 rounded w-20"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Loading animations */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .task-card {
        @apply flex-col space-y-3;
    }
    
    .task-card .task-actions {
        @apply flex-col space-y-2 space-x-0 w-full;
    }
    
    .task-card .task-actions button {
        @apply w-full justify-center;
    }
    
    .task-stats-grid {
        @apply grid-cols-2 gap-2;
    }
    
    .task-filters {
        @apply flex-col space-y-2 space-x-0;
    }
    
    .task-filter-tabs {
        @apply flex-wrap gap-1;
    }
}

@media (max-width: 640px) {
    .task-stats-grid {
        @apply grid-cols-1 gap-3;
    }
    
    .task-header {
        @apply flex-col items-start space-y-3;
    }
    
    .role-filter-dropdown {
        @apply w-full;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .task-card {
        @apply bg-gray-800 border-gray-700;
    }
    
    .task-card h3 {
        @apply text-gray-100;
    }
    
    .task-card p {
        @apply text-gray-300;
    }
    
    .task-skeleton > div {
        @apply bg-gray-700 border-gray-600;
    }
    
    .task-skeleton .bg-gray-200 {
        @apply bg-gray-600;
    }
}

/* Accessibility improvements */
.task-status-badge:focus {
    @apply ring-2 ring-offset-2 ring-blue-500 outline-none;
}

.task-priority-badge:focus {
    @apply ring-2 ring-offset-2 ring-orange-500 outline-none;
}

.task-action-button:focus {
    @apply ring-2 ring-offset-2 ring-indigo-500 outline-none;
}

/* Smooth transitions */
.task-card {
    @apply transition-all duration-200 ease-in-out;
}

.task-card:hover {
    @apply transform translate-y-1 shadow-lg;
}

.task-action-button {
    @apply transition-colors duration-150 ease-in-out;
}

/* Print styles */
@media print {
    .task-actions {
        @apply hidden;
    }
    
    .task-filters {
        @apply hidden;
    }
    
    .sidebar {
        @apply hidden;
    }
    
    .task-card {
        @apply break-inside-avoid mb-4;
    }
}
</style>

<script>
// Loading state management
function showTaskLoading() {
    document.getElementById('task-loading')?.classList.remove('hidden');
    document.getElementById('tasks-content')?.classList.add('hidden');
}

function hideTaskLoading() {
    document.getElementById('task-loading')?.classList.add('hidden');
    document.getElementById('tasks-content')?.classList.remove('hidden');
}

// Enhanced accessibility
document.addEventListener('DOMContentLoaded', function() {
    // Add keyboard navigation
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach((card, index) => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', `Task ${index + 1}`);
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const viewLink = card.querySelector('a[href*="tasks"]');
                if (viewLink) {
                    viewLink.click();
                }
            }
        });
    });
    
    // Add live regions for dynamic updates
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.className = 'sr-only';
    liveRegion.id = 'task-updates';
    document.body.appendChild(liveRegion);
});

// Status update with loading and accessibility
function updateTaskStatus(taskId, newStatus, button) {
    // Show loading state
    const originalText = button.textContent;
    button.textContent = 'Updating...';
    button.disabled = true;
    button.classList.add('opacity-50', 'cursor-not-allowed');
    
    // Add loading spinner
    const spinner = document.createElement('span');
    spinner.className = 'inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2';
    button.prepend(spinner);
    
    // Submit form (handled by Laravel)
    const form = button.closest('form');
    if (form) {
        form.submit();
    }
    
    // Announce to screen readers
    const liveRegion = document.getElementById('task-updates');
    if (liveRegion) {
        liveRegion.textContent = `Updating task status to ${newStatus}`;
    }
}

// Enhanced error handling
function handleTaskError(message) {
    // Show toast notification
    showNotification(message, 'error');
    
    // Announce to screen readers
    const liveRegion = document.getElementById('task-updates');
    if (liveRegion) {
        liveRegion.textContent = `Error: ${message}`;
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white border-l-4 p-4 shadow-lg rounded-r transition-all duration-300 transform translate-x-full`;
    
    // Add type-specific styling
    switch (type) {
        case 'error':
            notification.classList.add('border-red-500', 'bg-red-50');
            break;
        case 'success':
            notification.classList.add('border-green-500', 'bg-green-50');
            break;
        default:
            notification.classList.add('border-blue-500', 'bg-blue-50');
    }
    
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-1">
                <p class="text-sm text-gray-700">${message}</p>
            </div>
            <button class="flex-shrink-0 ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}
</script>