<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, unread, read
        
        $query = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
        
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        $notifications = $query->paginate(20);
        $unreadCount = Notification::where('user_id', Auth::id())->unread()->count();
        
        return view('notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }
    
    /**
     * Get recent notifications for dropdown (AJAX)
     */
    public function recent()
    {
        // Check authentication first
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $user = Auth::user();
        
        // Debug logging untuk troubleshoot user role issue
        Log::info('NotificationController::recent called', [
            'user_id' => $user->user_id ?? 'null',
            'user_role' => $user->role ?? 'null',
            'user_name' => $user->full_name ?? 'null',
        ]);
        
        try {
            $notifications = Notification::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'data' => $notification->data,
                        'is_read' => $notification->isRead(),
                        'created_at' => $notification->created_at->diffForHumans(),
                        'action_url' => $this->getActionUrl($notification),
                        'icon' => $this->getNotificationIcon($notification->type),
                        'color' => $this->getNotificationColor($notification->type),
                    ];
                });
                
            Log::info('NotificationController::recent result', [
                'notifications_count' => $notifications->count(),
                'user_id' => $user->user_id,
            ]);
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
            
        } catch (\Exception $e) {
            Log::error('NotificationController::recent error', [
                'error' => $e->getMessage(),
                'user_id' => $user->user_id ?? 'null'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }
    
    /**
     * Get unread notifications count (AJAX)
     */
    public function unreadCount()
    {
        // Check authentication first
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $user = Auth::user();
        
        Log::info('NotificationController::unreadCount called', [
            'user_id' => $user->user_id ?? 'null',
            'user_role' => $user->role ?? 'null',
        ]);
        
        try {
            $count = Notification::where('user_id', $user->user_id)
                ->unread()
                ->count();
                
            Log::info('NotificationController::unreadCount result', [
                'count' => $count,
                'user_id' => $user->user_id,
            ]);
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('NotificationController::unreadCount error', [
                'error' => $e->getMessage(),
                'user_id' => $user->user_id ?? 'null'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }
    
    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $notification->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $updated = Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated' => $updated
            ]);
        }
        
        return redirect()->back()->with('success', "All notifications marked as read ({$updated} notifications)");
    }
    
    /**
     * Delete a single notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $notification->delete();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }
        
        return redirect()->back()->with('success', 'Notification deleted');
    }
    
    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $deleted = Notification::where('user_id', Auth::id())
            ->read()
            ->delete();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Read notifications cleared',
                'deleted' => $deleted
            ]);
        }
        
        return redirect()->back()->with('success', "Read notifications cleared ({$deleted} notifications)");
    }
    
    /**
     * Get action URL based on notification data
     * 
     * @param Notification $notification Notification instance
     * @return string Action URL
     */
    private function getActionUrl($notification)
    {
        /** @var Notification $notification */
        
        try {
            $data = $notification->data;
            $user = Auth::user();
            
            // Handle different notification types safely
            switch ($notification->type) {
                case 'project_completed':
                    // For project completion notifications, admin goes to dashboard
                    if ($user->role === 'admin') {
                        return route('dashboard'); // admin dashboard
                    }
                    return route('dashboard'); // fallback to general dashboard
                    
                case 'task_assigned':
                case 'task_status_changed':
                case 'task_overdue':
                    // For task notifications, provide safe defaults based on user role
                    if ($user->role === 'admin') {
                        return route('dashboard');
                    } elseif ($user->role === 'leader') {
                        return route('leader.dashboard');
                    } else {
                        return route('dashboard'); // general dashboard for developer/designer
                    }
                    
                case 'extension_requested':
                    if ($user->role === 'leader') {
                        return route('leader.dashboard');
                    }
                    return route('dashboard');
                    
                case 'extension_approved':
                case 'extension_rejected':
                    if ($user->role === 'developer' || $user->role === 'designer') {
                        return route('dashboard');
                    }
                    return route('dashboard');
                    
                default:
                    // Safe default based on user role
                    if ($user->role === 'admin') {
                        return route('dashboard');
                    } elseif ($user->role === 'leader') {
                        return route('leader.dashboard');
                    } else {
                        return route('dashboard');
                    }
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating action URL', [
                'notification_id' => $notification->id,
                'notification_type' => $notification->type,
                'error' => $e->getMessage()
            ]);
            
            // Return safe default
            return route('dashboard');
        }
    }
    
    /**
     * Get notification icon based on type
     * 
     * @param string $type Notification type
     * @return string Icon emoji
     */
    private function getNotificationIcon($type)
    {
        /** @var string $type */
        
        return match($type) {
            Notification::TYPE_TASK_ASSIGNED => '📋',
            Notification::TYPE_TASK_APPROVED => '✅',
            Notification::TYPE_TASK_REJECTED => '❌',
            Notification::TYPE_TASK_STATUS_CHANGED => '🔄',
            Notification::TYPE_TASK_COMMENT => '💬',
            Notification::TYPE_TASK_DEADLINE => '⏰',
            'task_overdue' => '🚨',
            'work_session_reminder' => '⏱️',
            'task_completed' => '🎉',
            'project_assigned' => '🎯',
            'extension_requested' => '⏰',
            'extension_approved' => '✅',
            'extension_rejected' => '❌',
            default => '🔔',
        };
    }
    
    /**
     * Get notification color based on type
     * 
     * @param string $type Notification type
     * @return string Color name
     */
    private function getNotificationColor($type)
    {
        /** @var string $type */
        
        return match($type) {
            Notification::TYPE_TASK_ASSIGNED => 'blue',
            Notification::TYPE_TASK_APPROVED => 'green',
            Notification::TYPE_TASK_REJECTED => 'red',
            Notification::TYPE_TASK_STATUS_CHANGED => 'blue',
            Notification::TYPE_TASK_COMMENT => 'purple',
            Notification::TYPE_TASK_DEADLINE => 'yellow',
            'task_overdue' => 'red',
            'work_session_reminder' => 'orange',
            'task_completed' => 'green',
            'project_assigned' => 'indigo',
            'extension_requested' => 'yellow',
            'extension_approved' => 'green',
            'extension_rejected' => 'red',
            default => 'gray',
        };
    }
}
