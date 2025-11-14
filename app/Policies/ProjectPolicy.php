<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view projects
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Admin, creator, leader, or project member can view
        return $user->role === 'admin' 
            || $project->creator_id === $user->user_id
            || $project->leader_id === $user->user_id
            || $project->members()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create projects
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin, creator, or leader can update
        return $user->role === 'admin'
            || $project->creator_id === $user->user_id
            || $project->leader_id === $user->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only admin or creator can delete
        return $user->role === 'admin' 
            || $project->creator_id === $user->user_id;
    }

    /**
     * Determine whether the user can add members to the project.
     */
    public function addMember(User $user, Project $project): bool
    {
        // Only leader can add members
        return $project->leader_id === $user->user_id;
    }

    /**
     * Determine whether the user can remove members from the project.
     */
    public function removeMember(User $user, Project $project): bool
    {
        // Leader or admin can remove members
        return $user->role === 'admin' 
            || $project->leader_id === $user->user_id;
    }

    /**
     * Determine whether the user can view project statistics.
     */
    public function viewStatistics(User $user, Project $project): bool
    {
        // Admin, leader, or project member can view stats
        return $user->role === 'admin'
            || $project->leader_id === $user->user_id
            || $project->members()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }
}
