<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * Create user from social provider
     */
    public function createSocialUser(SocialiteUser $socialUser, string $provider): User
    {
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'full_name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(str()->random(24)),
            'role' => 'user',
            'status' => 'active',
            'avatar' => $socialUser->getAvatar(),
            $provider . '_id' => $socialUser->getId(),
            $provider . '_token' => $socialUser->token,
        ]);
    }

    /**
     * Update user with social provider data
     */
    public function updateUserSocialData(User $user, SocialiteUser $socialUser, string $provider): void
    {
        $updates = [];
        
        // Update avatar if not set
        if (!$user->avatar && $socialUser->getAvatar()) {
            $updates['avatar'] = $socialUser->getAvatar();
        }
        
        // Update social provider info
        $updates[$provider . '_id'] = $socialUser->getId();
        $updates[$provider . '_token'] = $socialUser->token;
        
        if (!empty($updates)) {
            $user->update($updates);
        }
    }

    /**
     * Get redirect URL based on user role
     */
    public function getRedirectUrlForUser(User $user): string
    {
        if ($user->isAdmin()) {
            return '/dashboard';
        } elseif ($user->isLeader()) {
            return '/dashboard';
        } else {
            return '/dashboard';
        }
    }

    /**
     * Get welcome message based on user role
     */
    public function getWelcomeMessage(User $user): string
    {
        if ($user->isAdmin()) {
            return 'Welcome back, Admin!';
        } elseif ($user->isLeader()) {
            return 'Welcome back, Team Leader!';
        } else {
            return 'Welcome to ProjectHub!';
        }
    }
}
