<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'subtask_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'title',
        'description',
        'priority',
        'is_completed',
        'created_by',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    
    /**
     * Get the task that owns the subtask.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }
    
    /**
     * Get the user who created the subtask.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
    
    /**
     * Mark subtask as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
    
    /**
     * Mark subtask as incomplete
     */
    public function markAsIncomplete()
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
    
    /**
     * Scope untuk completed subtasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
    
    /**
     * Scope untuk incomplete subtasks
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }
    
    /**
     * Scope untuk subtasks berdasarkan priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
