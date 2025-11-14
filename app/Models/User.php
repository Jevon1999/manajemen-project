<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'full_name',
        'name', // For compatibility with social login
        'password',
        'role',
        'specialty',
        'status',
        'avatar',
        'email_verified_at',
        // Social provider fields - Google dan GitHub saja
        'google_id',
        'google_token',
        'github_id', 
        'github_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's display name (full_name or name or username)
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: $this->name ?: $this->username;
    }

    // Role checking methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isLeader()
    {
        return $this->role === 'leader';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isDeveloper()
    {
        return $this->role === 'user' && $this->specialty === 'developer';
    }

    public function isDesigner()
    {
        return $this->role === 'user' && $this->specialty === 'designer';
    }

    public function canManageProjects()
    {
        return in_array($this->role, ['admin', 'leader']);
    }

    public function canAssignLeaders()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Get the projects created by the user.
     */
    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by', 'user_id');
    }

    /**
     * Get the projects where user is the leader.
     */
    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'leader_id', 'user_id');
    }
    
    /**
     * Get the projects the user is a member of.
     */
    public function projectMemberships()
    {
        return $this->hasMany(ProjectMember::class, 'user_id', 'user_id');
    }
    
    /**
     * Get the tasks assigned to the user.
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'user_id');
    }
    
    /**
     * Get the tasks created by the user.
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by', 'user_id');
    }
    
    /**
     * Get the card assignments for the user.
     */
    public function cardAssignments()
    {
        return $this->hasMany(CardAssignment::class, 'user_id', 'user_id');
    }
    
    /**
     * Get the time logs for the user (old table).
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class, 'user_id', 'user_id');
    }

    /**
     * Get the time entries for the user (new table).
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'user_id', 'user_id');
    }
    
    /**
     * Get the comments made by the user (old table).
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'user_id');
    }

    /**
     * Get the card comments made by the user (new table).
     */
    public function cardComments()
    {
        return $this->hasMany(CardComment::class, 'user_id', 'user_id');
    }

    /**
     * Get the attachments uploaded by the user.
     */
    public function uploadedAttachments()
    {
        return $this->hasMany(CardAttachment::class, 'uploaded_by', 'user_id');
    }

    /**
     * Get the activity logs for the user.
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }
    
    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id')->orderBy('created_at', 'desc');
    }
    
    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }
}
