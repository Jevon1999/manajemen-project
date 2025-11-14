<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'card_id',
        'task_id',
        'requested_by',
        'reviewed_by',
        'reason',
        'old_deadline',
        'requested_deadline',
        'status',
        'rejection_reason',
        'reviewed_at',
    ];

    protected $casts = [
        'old_deadline' => 'date',
        'requested_deadline' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the card that owns the extension request
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }
    
    /**
     * Get the task that owns the extension request
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }
    
    /**
     * Get the entity (card or task) dynamically
     */
    public function entity()
    {
        if ($this->entity_type === 'task' && $this->task_id) {
            return $this->task;
        }
        return $this->card;
    }

    /**
     * Get the user who requested the extension
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    /**
     * Get the user who reviewed the extension
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get days requested for extension
     */
    public function getExtensionDays(): int
    {
        return $this->old_deadline->diffInDays($this->requested_deadline);
    }
}

