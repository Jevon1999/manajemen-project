<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Card $card): bool
    {
        // Admin, project leader, project member, or assigned user can view
        if ($user->role === 'admin') return true;
        
        $project = $card->board->project;
        
        return $project->leader_id === $user->user_id
            || $project->members()->where('user_id', $user->user_id)->exists()
            || $card->assignments()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Project members can create cards (checked at board level)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Card $card): bool
    {
        // Admin, project leader, or assigned user can update
        if ($user->role === 'admin') return true;
        
        $project = $card->board->project;
        
        return $project->leader_id === $user->user_id
            || $card->assignments()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Card $card): bool
    {
        // Admin or project leader can delete
        if ($user->role === 'admin') return true;
        
        $project = $card->board->project;
        return $project->leader_id === $user->user_id;
    }

    /**
     * Determine whether the user can log time on the card.
     */
    public function logTime(User $user, Card $card): bool
    {
        // Only assigned users can log time
        return $card->assignments()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can add comments to the card.
     */
    public function addComment(User $user, Card $card): bool
    {
        // Admin, project member, or assigned user can comment
        if ($user->role === 'admin') return true;
        
        $project = $card->board->project;
        
        return $project->members()->where('user_id', $user->user_id)->exists()
            || $card->assignments()->where('user_id', $user->user_id)->exists();
    }

    /**
     * Determine whether the user can add attachments to the card.
     */
    public function addAttachment(User $user, Card $card): bool
    {
        // Same as comment - project member or assigned user
        return $this->addComment($user, $card);
    }

    /**
     * Determine whether the user can assign users to the card.
     */
    public function assignUsers(User $user, Card $card): bool
    {
        // Admin or project leader can assign users
        if ($user->role === 'admin') return true;
        
        $project = $card->board->project;
        return $project->leader_id === $user->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Card $card): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Card $card): bool
    {
        return $user->role === 'admin';
    }
}
