<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'project_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_name',
        'description',
        'created_by',
        'leader_id',
        'deadline',
        'status',
        'template_id',
        'priority',
        'category',
        'budget',
        'notifications_enabled',
        'public_visibility',
        'allow_member_invite',
        'completion_percentage',
        'last_activity_at',
        'is_archived',
        'completed_at',
        'delay_days',
        'delay_reason',
        'completion_notes',
        'is_overdue'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'notifications_enabled' => 'boolean',
        'public_visibility' => 'boolean',
        'allow_member_invite' => 'boolean',
        'is_archived' => 'boolean',
        'is_overdue' => 'boolean',
        'budget' => 'decimal:2',
        'completion_percentage' => 'integer',
        'delay_days' => 'integer'
    ];
    
    /**
     * Get the user that created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the project leader (assigned leader).
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id', 'user_id');
    }
    
    /**
     * Get the template used for this project.
     */
    public function template()
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }
    
    /**
     * Get the members of the project.
     */
    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id', 'project_id');
    }
    
    /**
     * Get the tasks of the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id', 'project_id');
    }

    /**
     * Get boards for this project.
     */
    public function boards()
    {
        return $this->hasMany(Board::class, 'project_id', 'project_id')
            ->orderBy('position');
    }

    /**
     * Get all cards through boards for this project.
     */
    public function cards()
    {
        return $this->hasManyThrough(
            Card::class,
            Board::class,
            'project_id', // Foreign key on boards table
            'board_id',   // Foreign key on cards table
            'project_id', // Local key on projects table
            'board_id'    // Local key on boards table
        );
    }

    /**
     * Get activity logs for this project.
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class, 'project_id', 'project_id');
    }
    
    /**
     * Get the project manager (legacy - using pivot).
     */
    public function projectManager()
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->wherePivot('role', 'project_manager')
            ->first();
    }

    /**
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_archived', false);
    }

    /**
     * Scope for projects by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for projects by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for public projects
     */
    public function scopePublic($query)
    {
        return $query->where('public_visibility', true);
    }

    /**
     * Scope for non-archived projects
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Get project status options
     */
    public static function getStatusOptions()
    {
        return [
            'planning' => 'Planning',
            'active' => 'Active',
            'completed' => 'Completed',
            'on-hold' => 'On Hold'
        ];
    }

    /**
     * Get project priority options
     */
    public static function getPriorityOptions()
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium', 
            'high' => 'High',
            'critical' => 'Critical'
        ];
    }

    /**
     * Get project category options
     */
    public static function getCategoryOptions()
    {
        return [
            'web_development' => 'Web Development',
            'mobile_app' => 'Mobile Application',
            'desktop_software' => 'Desktop Software',
            'data_analysis' => 'Data Analysis',
            'marketing' => 'Marketing',
            'design' => 'Design',
            'research' => 'Research',
            'other' => 'Other'
        ];
    }

    /**
     * Update project activity timestamp
     */
    public function touchActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Calculate project completion percentage
     */
    public function calculateCompletionPercentage()
    {
        $totalCards = $this->boards()->withCount('cards')->get()->sum('cards_count');
        
        if ($totalCards === 0) {
            return 0;
        }

        $completedCards = 0;
        foreach ($this->boards as $board) {
            // Assuming completed cards are in boards named 'Done', 'Completed', etc.
            if (in_array(strtolower($board->name), ['done', 'completed', 'finished'])) {
                $completedCards += $board->cards()->count();
            }
        }

        $percentage = round(($completedCards / $totalCards) * 100);
        
        // Update the completion percentage in database
        $this->update(['completion_percentage' => $percentage]);
        
        return $percentage;
    }

    /**
     * Archive the project
     */
    public function archive()
    {
        $this->update([
            'is_archived' => true,
            'status' => 'completed'
        ]);
    }

    /**
     * Unarchive the project
     */
    public function unarchive()
    {
        $this->update(['is_archived' => false]);
    }

    /**
     * Check if project can be completed
     * All tasks must be in 'done' status
     */
    public function canBeCompleted()
    {
        $totalTasks = $this->boards->sum(function($board) {
            return $board->cards->count();
        });
        
        if ($totalTasks === 0) {
            return false;
        }
        
        $completedTasks = $this->boards->sum(function($board) {
            return $board->cards->where('status', 'done')->count();
        });
        
        return $totalTasks === $completedTasks;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage()
    {
        $totalTasks = $this->boards->sum(function($board) {
            return $board->cards->count();
        });
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->boards->sum(function($board) {
            return $board->cards->where('status', 'done')->count();
        });
        
        return (int) (($completedTasks / $totalTasks) * 100);
    }

    /**
     * Check if project is currently overdue (not completed yet)
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || !$this->deadline) {
            return false;
        }
        
        return now()->isAfter($this->deadline);
    }

    /**
     * Calculate delay in days between deadline and completion
     */
    public function calculateDelay(): int
    {
        if (!$this->deadline || !$this->completed_at) {
            return 0;
        }
        
        // Return positive number if late, 0 if on time
        $delay = $this->deadline->diffInDays($this->completed_at, false);
        return max(0, (int) $delay);
    }

    /**
     * Mark project as completed with optional notes and delay reason
     */
    public function markAsCompleted(?string $notes = null, ?string $delayReason = null): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completion_notes = $notes;
        $this->completion_percentage = 100;
        
        // Check if overdue
        if ($this->deadline && now()->isAfter($this->deadline)) {
            $this->is_overdue = true;
            $this->delay_days = now()->diffInDays($this->deadline);
            $this->delay_reason = $delayReason;
        } else {
            $this->is_overdue = false;
            $this->delay_days = 0;
            $this->delay_reason = null;
        }
        
        $this->touchActivity();
        $this->save();
    }

    /**
     * Scope: Get overdue projects (not completed yet)
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->whereNotNull('deadline')
                     ->where('deadline', '<', now());
    }

    /**
     * Scope: Get projects completed on time
     */
    public function scopeCompletedOnTime($query)
    {
        return $query->where('status', 'completed')
                     ->where('is_overdue', false);
    }

    /**
     * Scope: Get projects completed but late
     */
    public function scopeCompletedLate($query)
    {
        return $query->where('status', 'completed')
                     ->where('is_overdue', true);
    }

    /**
     * Get delay status badge color
     */
    public function getDelayBadgeColor(): string
    {
        if (!$this->is_overdue) {
            return 'green';
        }
        
        if ($this->delay_days <= 3) {
            return 'yellow';
        } elseif ($this->delay_days <= 7) {
            return 'orange';
        } else {
            return 'red';
        }
    }

    /**
     * Get formatted delay message
     */
    public function getDelayMessage(): string
    {
        if (!$this->is_overdue) {
            return 'Selesai tepat waktu';
        }
        
        return "Terlambat {$this->delay_days} hari";
    }
}
