<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class SocialiteController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirectToProvider($provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback($provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'social' => 'Authentication failed. Please try again.'
            ]);
        }

        // Check if user already exists
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Update social provider info if not set
            $this->updateSocialInfo($existingUser, $provider, $socialUser);
            Auth::login($existingUser);
        } else {
            // Create new user
            $user = $this->createUserFromSocial($socialUser, $provider);
            Auth::login($user);
        }

        // Redirect based on role
        $user = Auth::user();
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Create user from social provider data
     */
    private function createUserFromSocial($socialUser, $provider)
    {
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        
        return User::create([
            'username' => $this->generateUsername($name),
            'full_name' => $name,
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(str()->random(24)), // Random password for social users
            'role' => 'user', // Default role
            'status' => 'active',
            'avatar' => $socialUser->getAvatar(),
            $provider . '_id' => $socialUser->getId(),
            $provider . '_token' => $socialUser->token,
        ]);
    }

    /**
     * Generate unique username from name
     */
    private function generateUsername($name)
    {
        $username = strtolower(str_replace(' ', '', $name));
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = strtolower(str_replace(' ', '', $name)) . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Update existing user with social info
     */
    private function updateSocialInfo($user, $provider, $socialUser)
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
     * Redirect user based on role
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended('/dashboard')->with('success', 'Welcome back, Admin!');
        } elseif ($user->role === 'leader') {
            return redirect()->intended('/dashboard')->with('success', 'Welcome back, Team Leader!');
        } else {
            return redirect()->intended('/dashboard')->with('success', 'Welcome to ProjectHub!');
        }
    }

    /**
     * Validate social provider - hanya Google dan GitHub
     */
    private function validateProvider($provider)
    {
        $allowedProviders = ['google', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            abort(404, 'Provider not supported');
        }
    }

    /**
     * Unlink social account
     */
    public function unlinkProvider(Request $request, $provider)
    {
        $this->validateProvider($provider);
        
        $user = Auth::user();
        
        // Check if user has password set (can't unlink if no password)
        if (!$user->password) {
            return back()->withErrors([
                'social' => 'Please set a password before unlinking your social account.'
            ]);
        }
        
        // Update user model with null values to unlink social account
        $updates = [
            $provider . '_id' => null,
            $provider . '_token' => null,
        ];
        
        User::where('user_id', $user->user_id)->update($updates);
        
        return back()->with('success', ucfirst($provider) . ' account unlinked successfully.');
    }
}
