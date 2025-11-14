@extends('layout.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🔔 Notifikasi</h1>
            <p class="text-gray-600 mt-2">Kelola semua notifikasi Anda</p>
        </div>
        
        <!-- Unread Count Badge -->
        @if($unreadCount > 0)
        <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg">
            <span class="font-semibold">{{ $unreadCount }}</span> belum dibaca
        </div>
        @endif
    </div>
    
    <!-- Action Bar -->
    <div class="bg-white shadow-sm rounded-lg p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex space-x-2">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Semua
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Belum Dibaca
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'read' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Sudah Dibaca
            </a>
        </div>
        
        <!-- Bulk Actions -->
        <div class="flex space-x-2">
            @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    📖 Tandai Semua Dibaca
                </button>
            </form>
            @endif
            
            @if($notifications->where('read_at', '!=', null)->count() > 0)
            <form action="{{ route('notifications.clear-read') }}" method="POST" class="inline" 
                  onsubmit="return confirm('Hapus semua notifikasi yang sudah dibaca?')">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors">
                    🗑️ Hapus yang Sudah Dibaca
                </button>
            </form>
            @endif
        </div>
    </div>
    
    <!-- Notifications List -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        @forelse($notifications as $notification)
        <div class="border-b border-gray-200 {{ !$notification->isRead() ? 'bg-blue-50' : 'bg-white' }} hover:bg-gray-50 transition-colors">
            <div class="p-6 flex items-start space-x-4">
                <!-- Icon -->
                <div class="flex-shrink-0 text-3xl">
                    @php
                        $icon = match($notification->type) {
                            'task_assigned' => '📋',
                            'task_approved' => '✅',
                            'task_rejected' => '❌',
                            'task_status_changed' => '🔄',
                            'task_comment' => '💬',
                            'task_deadline' => '⏰',
                            'task_overdue' => '🚨',
                            'work_session_reminder' => '⏱️',
                            'task_completed' => '🎉',
                            default => '🔔',
                        };
                        
                        $colorClass = match($notification->type) {
                            'task_assigned' => 'border-blue-200',
                            'task_approved' => 'border-green-200',
                            'task_rejected' => 'border-red-200',
                            'task_status_changed' => 'border-blue-200',
                            'task_comment' => 'border-purple-200',
                            'task_deadline' => 'border-yellow-200',
                            'task_overdue' => 'border-red-300',
                            'work_session_reminder' => 'border-orange-200',
                            'task_completed' => 'border-green-200',
                            default => 'border-gray-200',
                        };
                    @endphp
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white border-2 {{ $colorClass }}">
                        {{ $icon }}
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-semibold text-gray-900">{{ $notification->title }}</h3>
                            @if(!$notification->isRead())
                            <span class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></span>
                            @endif
                        </div>
                        <span class="text-sm text-gray-500 whitespace-nowrap ml-4">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    <p class="text-sm text-gray-700 mb-3">{{ $notification->message }}</p>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3">
                        @if($notification->data && isset($notification->data['task_id']))
                        <a href="{{ route(Auth::user()->isDeveloper() || Auth::user()->isDesigner() ? 'developer.tasks.show' : (Auth::user()->isLeader() ? 'leader.tasks.show' : 'admin.tasks.show'), $notification->data['task_id']) }}" 
                           onclick="markAsRead({{ $notification->id }})"
                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Lihat Task
                        </a>
                        @endif
                        
                        @if(!$notification->isRead())
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Tandai Dibaca
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                    
                    <!-- Additional Info -->
                    @if($notification->data && isset($notification->data['project_name']))
                    <div class="mt-2 text-xs text-gray-500">
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Project: {{ $notification->data['project_name'] }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <p class="text-xl font-semibold text-gray-500 mb-2">Tidak ada notifikasi</p>
            <p class="text-gray-400">
                @if($filter === 'unread')
                    Semua notifikasi sudah dibaca
                @elseif($filter === 'read')
                    Belum ada notifikasi yang dibaca
                @else
                    Notifikasi Anda akan muncul di sini
                @endif
            </p>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
}
</script>
@endsection
