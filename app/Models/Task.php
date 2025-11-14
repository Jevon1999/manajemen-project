<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'task_id';
    
    protected $fillable = [
        'project_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'deadline',
        'completed_at',
        'rejection_reason',
        'is_blocked',
        'block_reason',
    ];
    
    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'is_blocked' => 'boolean',
    ];
    
    /**
     * Status constants
     */
    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REVIEW = 'review';
    const STATUS_DONE = 'done';
    const STATUS_OVERDUE = 'overdue';
    
    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    
    /**
     * Get the project that owns the task
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
    
    /**
     * Get the user assigned to the task
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
    
    /**
     * Get the user who created the task
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
    
    /**
     * Get the subtasks for the task.
     */
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'task_id', 'task_id');
    }
    
    /**
     * Get the time logs for the task.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class, 'task_id', 'task_id');
    }
    
    /**
     * Get active (running) time log for this task
     */
    public function activeTimeLog()
    {
        return $this->hasOne(TimeLog::class, 'task_id', 'task_id')
                    ->whereNull('end_time')
                    ->latest('start_time');
    }
    
    /**
     * Get the comments for the task.
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id', 'task_id')
                    ->with('user')
                    ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get total time spent on this task (in seconds)
     */
    public function getTotalTimeSpentAttribute()
    {
        return $this->timeLogs()->completed()->sum('duration_seconds') ?? 0;
    }
    
    /**
     * Get formatted total time spent (HH:MM:SS)
     */
    public function getFormattedTotalTimeAttribute()
    {
        $seconds = $this->total_time_spent;
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    
    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope for filtering by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    
    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->whereNotIn('status', [self::STATUS_DONE]);
    }
    
    /**
     * Scope for assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
    
    /**
     * Check if task is overdue
     */
    public function isOverdue()
    {
        return $this->deadline && 
               $this->deadline->isPast() && 
               $this->status !== self::STATUS_DONE;
    }
    
    /**
     * Get status label with icon
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_TODO => '📋 To Do',
            self::STATUS_IN_PROGRESS => '🚀 In Progress',
            self::STATUS_REVIEW => '👀 Review',
            self::STATUS_DONE => '✅ Done',
            self::STATUS_OVERDUE => '⚠️ Overdue',
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    /**
     * Get priority label with icon
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => '🟢 Low',
            self::PRIORITY_MEDIUM => '🟡 Medium',
            self::PRIORITY_HIGH => '🔴 High',
        ];
        
        return $labels[$this->priority] ?? $this->priority;
    }
    
    /**
     * Get status badge color class
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_TODO => 'bg-gray-100 text-gray-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_REVIEW => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DONE => 'bg-green-100 text-green-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
        ];
        
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
    
    /**
     * Get priority badge color class
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            self::PRIORITY_LOW => 'bg-green-100 text-green-800',
            self::PRIORITY_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_HIGH => 'bg-red-100 text-red-800',
        ];
        
        return $colors[$this->priority] ?? 'bg-gray-100 text-gray-800';
    }
    
    /**
     * Get extension requests for this task
     */
    public function extensionRequests()
    {
        return $this->hasMany(ExtensionRequest::class, 'card_id', 'task_id');
    }
    
    /**
     * Get pending extension request
     */
    public function pendingExtensionRequest()
    {
        return $this->hasOne(ExtensionRequest::class, 'card_id', 'task_id')
                    ->where('status', 'pending')
                    ->latest();
    }
    
    /**
     * Check if task has pending extension request
     */
    public function hasPendingExtensionRequest()
    {
        return $this->extensionRequests()
                    ->where('status', 'pending')
                    ->exists();
    }
    
    /**
     * Check if task has approved extension
     */
    public function hasApprovedExtension()
    {
        return $this->extensionRequests()
                    ->where('status', 'approved')
                    ->exists();
    }
    
    /**
     * Block the task with a reason
     */
    public function block($reason)
    {
        $this->is_blocked = true;
        $this->block_reason = $reason;
        $this->save();
    }
    
    /**
     * Unblock the task
     */
    public function unblock()
    {
        $this->is_blocked = false;
        $this->block_reason = null;
        $this->save();
    }
}
