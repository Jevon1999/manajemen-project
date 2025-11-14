// Real-time Notifications Handler
// This file handles WebSocket connections and displays real-time notifications

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    const userId = document.querySelector('meta[name="user-id"]')?.content;
    
    if (!userId || !window.Echo) {
        console.log('Echo not initialized or user not authenticated');
        return;
    }

    console.log('Initializing real-time notifications for user:', userId);

    // Listen for task assigned notifications
    window.Echo.private(`user.${userId}`)
        .listen('.task.assigned', (e) => {
            console.log('Task assigned:', e);
            showNotification({
                title: '🎯 New Task Assigned',
                message: e.message,
                type: 'info',
                data: e
            });
            
            // Update notification badge
            updateNotificationBadge();
        });

    // Listen for task status changes
    window.Echo.private(`user.${userId}`)
        .listen('.task.status.changed', (e) => {
            console.log('Task status changed:', e);
            showNotification({
                title: '🔄 Task Status Updated',
                message: e.message,
                type: 'warning',
                data: e
            });
            
            // Update notification badge only - NO AUTO RELOAD
            // Let user actions (button clicks) handle page refresh
            updateNotificationBadge();
        });

    // Show notification toast
    function showNotification(notification) {
        // Create notification element
        const notifEl = document.createElement('div');
        notifEl.className = 'fixed top-20 right-4 z-50 max-w-sm bg-white rounded-lg shadow-2xl border-l-4 border-blue-500 p-4 animate-slide-in-right';
        notifEl.innerHTML = `
            <div class="flex items-start">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">${notification.title}</h4>
                    <p class="text-sm text-gray-600">${notification.message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notifEl);
        
        // Play notification sound (optional)
        playNotificationSound();
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notifEl.classList.add('animate-fade-out');
            setTimeout(() => {
                notifEl.remove();
            }, 300);
        }, 5000);
    }

    // Update notification badge count
    function updateNotificationBadge() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.classList.remove('hidden');
        }
    }

    // Play notification sound
    function playNotificationSound() {
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBCx7zPLMeScFJHfH8N+SSQsWYrjq6Z1RFAxPpuPyvmwfBCR5zPLOfSgFKHnL8N+SSQsVYrjp659SEAxRpuPyu2wfBCR5zPLOfSgFKHnL8OCSSQsVYrjp66BSEA==');
            audio.volume = 0.3;
            audio.play();
        } catch (e) {
            console.log('Could not play notification sound');
        }
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in-right {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes fade-out {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out;
        }
        .animate-fade-out {
            animation: fade-out 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);

    console.log('Real-time notifications initialized successfully');
});
