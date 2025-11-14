<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'member_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // Role checking methods
    public function isProjectManager()
    {
        return $this->role === 'project_manager';
    }

    public function isDeveloper()
    {
        return $this->role === 'developer';
    }

    public function isDesigner()
    {
        return $this->role === 'designer';
    }

    public function canManageTasks()
    {
        return $this->role === 'project_manager';
    }
    
    /**
     * Get the project that the member belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
    
    /**
     * Get the user that is a member.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
