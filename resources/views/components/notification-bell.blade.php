<!-- Notification Bell Component -->
<div x-data="notificationBell()" x-init="init()" class="relative">
    <!-- Bell Button -->
    <button @click="toggleDropdown()" 
            class="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Badge Count -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">
        </span>
        
        <!-- Pulse Animation for New Notifications -->
        <span x-show="unreadCount > 0" 
              class="absolute -top-1 -right-1 flex h-5 w-5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
        </span>
    </button>
    
    <!-- Dropdown -->
    <div x-show="open" 
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute top-full mt-2 right-0 w-80 sm:w-96 md:w-[28rem] bg-white rounded-xl shadow-2xl border border-gray-200 z-50 overflow-hidden"
        style="display: none;">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-3 py-2.5 sm:px-4 sm:py-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-white">Notifikasi</h3>
            <button @click="markAllAsRead()" 
                    x-show="unreadCount > 0"
                    class="text-xs text-white hover:text-indigo-100 font-medium">
                Tandai Semua Dibaca
            </button>
        </div>
        
    <!-- Notifications List -->
    <div class="max-h-[60vh] overflow-y-auto">
            <!-- Loading State -->
            <div x-show="loading" class="p-4 text-center">
                <svg class="animate-spin h-6 w-6 sm:h-8 sm:w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-xs sm:text-sm text-gray-500 mt-2">Memuat notifikasi...</p>
            </div>
            
            <!-- Notifications -->
            <template x-if="!loading && notifications.length > 0">
                <div class="divide-y divide-gray-100">
                    <template x-for="notification in notifications" :key="notification.id">
                        <div @click="handleNotificationClick(notification)"
                             :class="{'bg-blue-50': !notification.is_read, 'bg-white hover:bg-gray-50': notification.is_read}"
                             class="px-3 py-3 sm:px-4 sm:py-4 cursor-pointer transition-colors">
                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                                    </svg>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <p class="text-xs sm:text-sm font-semibold text-gray-900 pr-2 leading-snug break-words" x-text="notification.title"></p>
                                        <!-- Unread Indicator -->
                                        <span x-show="!notification.is_read" 
                                              class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5 leading-relaxed break-words" x-text="notification.message"></p>
                                    <p class="text-[10px] sm:text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            
            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" 
                 class="px-4 py-8 sm:py-12 text-center">
                <div class="bg-gray-100 rounded-full p-3 sm:p-4 mb-3 inline-block">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <p class="text-xs sm:text-sm font-medium text-gray-900 mb-1">Tidak ada notifikasi</p>
                <p class="text-[10px] sm:text-xs text-gray-500">Semua notifikasi Anda akan muncul di sini</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="border-t border-gray-100 px-3 py-2.5 sm:px-4 sm:py-3 bg-gray-50">
            <a href="{{ route('notifications.index') }}" 
               class="block text-center text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                Lihat Semua Notifikasi →
            </a>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        pollInterval: null,
        
        init() {
            this.fetchNotifications();
            this.fetchUnreadCount();
            
            // Poll for new notifications every 30 seconds
            this.pollInterval = setInterval(() => {
                this.fetchUnreadCount();
            }, 30000);
        },
        
        toggleDropdown() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },
        
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch('{{ route('notifications.recent') }}');
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.notifications;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async fetchUnreadCount() {
            try {
                const response = await fetch('{{ route('notifications.unread-count') }}');
                const data = await response.json();
                if (data.success) {
                    this.unreadCount = data.count;
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        },
        
        async handleNotificationClick(notification) {
            // Mark as read
            if (!notification.is_read) {
                await this.markAsRead(notification.id);
            }
            
            // Navigate to action URL
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
            
            this.open = false;
        },
        
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    // Update local state
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                    }
                    this.fetchUnreadCount();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    // Update all notifications to read
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;
                    
                    // Show success message
                    this.showToast('Semua notifikasi telah ditandai sebagai dibaca', 'success');
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },
        
        showToast(message, type = 'info') {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}
</script>
