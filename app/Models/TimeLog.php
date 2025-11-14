<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeLog extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'time_logs';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'timelog_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'start_time',
        'end_time',
        'duration_seconds',
        'notes',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_seconds' => 'integer',
    ];
    
    /**
     * Get the task that this time log belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }
    
    /**
     * Get the user who logged this time.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    /**
     * Stop the timer and calculate duration
     */
    public function stopTimer()
    {
        if ($this->end_time) {
            return; // Already stopped
        }
        
        $this->end_time = now();
        $this->duration_seconds = $this->end_time->diffInSeconds($this->start_time);
        $this->save();
    }
    
    /**
     * Get formatted duration (HH:MM:SS)
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return '00:00:00';
        }
        
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Get human-readable duration
     */
    public function getHumanDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }
        
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$minutes}m";
    }
    
    /**
     * Check if timer is currently running
     */
    public function isRunning()
    {
        return $this->end_time === null;
    }
    
    /**
     * Get elapsed time for running timer (in seconds)
     */
    public function getElapsedSeconds()
    {
        if (!$this->isRunning()) {
            return $this->duration_seconds;
        }
        
        return now()->diffInSeconds($this->start_time);
    }
    
    /**
     * Scope: Get only running timers
     */
    public function scopeRunning($query)
    {
        return $query->whereNull('end_time');
    }
    
    /**
     * Scope: Get completed timers
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_time');
    }
    
    /**
     * Scope: Get logs for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }
    
    /**
     * Scope: Get logs between dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }
}
