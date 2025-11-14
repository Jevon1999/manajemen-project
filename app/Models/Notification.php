<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Notification types constants
     */
    const TYPE_TASK_ASSIGNED = 'task_assigned';
    const TYPE_TASK_STATUS_CHANGED = 'task_status_changed';
    const TYPE_TASK_COMMENT = 'task_comment';
    const TYPE_TASK_DEADLINE = 'task_deadline';
    const TYPE_TASK_APPROVED = 'task_approved';
    const TYPE_TASK_REJECTED = 'task_rejected';
    
    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
    
    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->read_at = now();
            $this->save();
        }
    }
    
    /**
     * Determine if the notification has been read.
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }
}
