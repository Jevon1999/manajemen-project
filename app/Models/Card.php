<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'card_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'board_id',
        'card_title',
        'description',
        'created_by',
        'due_date',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'is_active',
        'is_blocked',
        'block_reason',
        'requires_approval',
        'approved_by',
        'approved_at',
        'last_progress_update',
        'has_time_log_today',
        'assignment_score',
        'started_at',
        'completed_at',
        'last_overdue_alert_at',
        'last_escalation_at',
        'overdue_notification_count',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'last_progress_update' => 'datetime',
        'has_time_log_today' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_overdue_alert_at' => 'datetime',
        'last_escalation_at' => 'datetime',
        'overdue_notification_count' => 'integer',
    ];
    
    /**
     * Get the board that owns the card.
     */
    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }
    
    /**
     * Get the user that created the card.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
    
    /**
     * Get the user who approved the card.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }
    
    /**
     * Get the subtasks for the card.
     */
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'card_id', 'card_id')
            ->orderBy('position');
    }
    
    /**
     * Get the assignments for the card.
     */
    public function assignments()
    {
        return $this->hasMany(CardAssignment::class, 'card_id', 'card_id');
    }
    
    /**
     * Get the time logs for the card.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class, 'card_id', 'card_id');
    }
    
    /**
     * Get the comments for the card (old table).
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'card_id', 'card_id');
    }

    /**
     * Get the card comments for the card (new table).
     */
    public function cardComments()
    {
        return $this->hasMany(CardComment::class, 'card_id', 'card_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the task comments (collaboration comments)
     */
    public function taskComments()
    {
        return $this->hasMany(TaskComment::class, 'card_id', 'card_id')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the attachments for the card (old table).
     */
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class, 'card_id', 'card_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the card attachments for the card (new table).
     */
    public function cardAttachments()
    {
        return $this->hasMany(CardAttachment::class, 'card_id', 'card_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get time entries for this card (new table).
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'card_id', 'card_id')
            ->orderBy('work_date', 'desc');
    }

    /**
     * Get activity logs for this card.
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class, 'card_id', 'card_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get active time log (running timer)
     */
    public function activeTimeLog()
    {
        return $this->hasOne(TimeLog::class, 'card_id', 'card_id')
            ->whereNull('end_time')
            ->latest();
    }

    /**
     * Scope for tasks assigned to a specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignments', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope for active tasks (not completed/cancelled)
     */
    public function scopeActive($query)
    {
        // Include overdue as an active state so users with overdue tasks are considered busy
        return $query->whereIn('status', ['todo', 'in_progress', 'review', 'overdue']);
    }

    /**
     * Check if user has active task (including overdue tasks)
     */
    public static function userHasActiveTask($userId)
    {
        return self::assignedTo($userId)
            ->where(function($query) {
                $query->where('is_active', true)
                      ->orWhere('status', 'overdue');
            })
            ->exists();
    }
    
    /*
    |--------------------------------------------------------------------------
    | BUSINESS RULES IMPLEMENTATION
    |--------------------------------------------------------------------------
    */
    
    /**
     * Rule 1: Get active task for a specific developer (including overdue)
     * Enforce: One active task per developer
     */
    public static function getActivetaskForUser($userId)
    {
        return self::assignedTo($userId)
            ->where(function($query) {
                $query->where('is_active', true)
                      ->orWhere('status', 'overdue');
            })
            ->first();
    }
    
    /**
     * Rule 1: Check if developer can take a new task
     */
    public static function canUserTakeNewTask($userId)
    {
        return !self::userHasActiveTask($userId);
    }
    
    /**
     * Rule 2: Check if task has time log today
     */
    public function hasTimeLogToday()
    {
        return $this->timeLogs()
            ->whereDate('start_time', today())
            ->exists();
    }
    
    /**
     * Rule 3: Check if task has comment today
     */
    public function hasCommentToday()
    {
        return $this->comments()
            ->whereDate('created_at', today())
            ->exists();
    }
    
    /**
     * Rule 3: Check if daily update is required
     */
    public function needsDailyUpdate()
    {
        if (!$this->is_active) {
            return false;
        }
        
        // If last update was not today, needs update
        if (!$this->last_progress_update || !$this->last_progress_update->isToday()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Rule 4: Check if task is approved
     */
    public function isApproved()
    {
        return $this->approved_at !== null && $this->approved_by !== null;
    }
    
    /**
     * Rule 4: Check if task can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'review' 
            && $this->requires_approval 
            && !$this->isApproved();
    }
    
    /**
     * Rule 4: Approve the task
     */
    public function approve($userId)
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Task cannot be approved in current state');
        }
        
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
            'status' => 'completed',
            'is_active' => false,
        ]);
        
        return $this;
    }
    
    /**
     * Rule 5: Calculate assignment score based on priority
     */
    public function calculateAssignmentScore()
    {
        $priorityScores = [
            'urgent' => 100,
            'high' => 75,
            'medium' => 50,
            'low' => 25,
        ];
        
        $score = $priorityScores[$this->priority] ?? 50;
        
        // Add urgency based on due date
        if ($this->due_date) {
            $daysUntilDue = now()->diffInDays($this->due_date, false);
            if ($daysUntilDue < 0) {
                $score += 50; // Overdue
            } elseif ($daysUntilDue <= 3) {
                $score += 30; // Due soon
            } elseif ($daysUntilDue <= 7) {
                $score += 15; // Due this week
            }
        }
        
        $this->update(['assignment_score' => $score]);
        return $score;
    }
    
    /**
     * Start working on this task
     */
    public function startWork($userId)
    {
        // Check if user already has active task
        if (!self::canUserTakeNewTask($userId)) {
            throw new \Exception('Developer already has an active task. Complete it before starting a new one.');
        }
        
        $this->update([
            'is_active' => true,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        
        return $this;
    }
    
    /**
     * Complete the task (requires approval)
     */
    public function markAsComplete()
    {
        // Check if has time log today
        if (!$this->hasTimeLogToday()) {
            throw new \Exception('Time tracking is required before marking task as complete');
        }
        
        $this->update([
            'status' => 'review',
            'completed_at' => now(),
            'is_active' => false,
        ]);
        
        return $this;
    }
    
    /**
     * Update progress with required validations
     */
    public function updateProgress($data, $userId)
    {
        // Rule 2: Time tracking mandatory
        if (!$this->hasTimeLogToday()) {
            throw new \Exception('You must log time before updating progress');
        }
        
        // Rule 3: Daily comment required
        if ($this->needsDailyUpdate() && empty($data['comment'])) {
            throw new \Exception('Daily progress comment is required');
        }
        
        // Add comment if provided
        if (!empty($data['comment'])) {
            $this->comments()->create([
                'user_id' => $userId,
                'comment_text' => $data['comment'],
                'created_at' => now(),
            ]);
            
            $this->update(['last_progress_update' => now()]);
        }
        
        return $this;
    }
    
    /**
     * Scope: Get tasks ready for assignment (priority-based)
     */
    public function scopeReadyForAssignment($query)
    {
        return $query->where('status', 'todo')
            ->orderByDesc('assignment_score')
            ->orderByDesc('priority')
            ->orderBy('due_date', 'asc');
    }
    
    /**
     * Scope: Get tasks needing daily update
     */
    public function scopeNeedsDailyUpdate($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('last_progress_update')
                  ->orWhereDate('last_progress_update', '<', today());
            });
    }
    
    /**
     * Scope: Get tasks pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'review')
            ->where('requires_approval', true)
            ->whereNull('approved_at');
    }
    
    /**
     * Get extension requests for this card
     */
    public function extensionRequests()
    {
        return $this->hasMany(ExtensionRequest::class, 'card_id', 'card_id');
    }
    
    /**
     * Get pending extension request
     */
    public function pendingExtensionRequest()
    {
        return $this->hasOne(ExtensionRequest::class, 'card_id', 'card_id')
            ->where('status', 'pending')
            ->latest();
    }
    
    /**
     * Check if card has pending extension request
     */
    public function hasPendingExtensionRequest(): bool
    {
        return $this->pendingExtensionRequest()->exists();
    }
    
    /**
     * Block the card
     * 
     * @param string $reason Block reason
     * @return void
     */
    public function block(string $reason = 'Task is overdue'): void
    {
        $this->update([
            'is_blocked' => true,
            'block_reason' => $reason,
        ]);
    }
    
    /**
     * Unblock the card
     * 
     * @return void
     */
    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'block_reason' => null,
        ]);
    }
    
    /**
     * Check if card has approved extension
     */
    public function hasApprovedExtension(): bool
    {
        return $this->extensionRequests()
            ->where('status', 'approved')
            ->where('requested_deadline', '>=', now())
            ->exists();
    }
}


