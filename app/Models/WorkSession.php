<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'session_id';
    
    protected $fillable = [
        'user_id',
        'task_id',
        'started_at',
        'stopped_at',
        'paused_at',
        'pause_duration',
        'duration_seconds',
        'work_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'paused_at' => 'datetime',
        'work_date' => 'date',
        'duration_seconds' => 'integer',
        'pause_duration' => 'integer'
    ];

    /**
     * Get the user that owns the work session
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the task associated with the work session
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    /**
     * Get formatted duration (HH:MM:SS)
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Get duration in hours (decimal)
     */
    public function getDurationHoursAttribute()
    {
        return round($this->duration_seconds / 3600, 2);
    }

    /**
     * Scope to get active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get sessions for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('work_date', $date);
    }

    /**
     * Scope to get sessions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
