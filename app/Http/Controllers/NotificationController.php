<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $notifications = Notification::where('user_id', Auth::id())
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
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Get unread notifications count (AJAX)
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
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
        
        $data = $notification->data;
        
        // Handle extension request notifications
        if ($notification->type === 'extension_requested') {
            // Leader goes to extension requests page
            return route('extension-requests.index');
        }
        
        if ($notification->type === 'extension_approved' || $notification->type === 'extension_rejected') {
            // Developer goes to the task
            $entityType = $data['entity_type'] ?? 'card';
            $entityId = $data['entity_id'] ?? $data['card_id'] ?? null;
            
            if ($entityId) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                if ($entityType === 'task') {
                    // For Task entity, go to task detail page
                    return route('tasks.show', $entityId);
                } else {
                    // For Card entity (board task), go to role-specific task page
                    if ($user->isDeveloper() || $user->isDesigner()) {
                        return route('developer.tasks.show', $entityId);
                    } elseif ($user->isLeader()) {
                        return route('leader.tasks.show', $entityId);
                    } elseif ($user->isAdmin()) {
                        return route('admin.tasks.show', $entityId);
                    }
                }
            }
            return route('tasks.index');
        }
        
        // Handle project notifications
        if ($notification->type === 'project_assigned' && isset($data['project_id'])) {
            return route('admin.projects.show', $data['project_id']);
        }
        
        // Default to tasks page if no specific URL
        if (!$data || !isset($data['task_id'])) {
            return route('tasks.index');
        }
        
        // Check user role for appropriate route
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($user->isDeveloper() || $user->isDesigner()) {
            return route('developer.tasks.show', $data['task_id']);
        } elseif ($user->isLeader()) {
            return route('leader.tasks.show', $data['task_id']);
        } elseif ($user->isAdmin()) {
            return route('admin.tasks.show', $data['task_id']);
        }
        
        return route('tasks.index');
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
