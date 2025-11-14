<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;


class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'username' => $this->generateUsername($request->name),
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default role
            'status' => 'active',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
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

    public function login(LoginRequest $request)
    {
        // Validate the request
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');
        
        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session to prevent fixation attacks
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user is active
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ])->onlyInput('email');
            }
            
            // Store user info in session
            $request->session()->put('user_role', $user->role);
            $request->session()->put('user_id', $user->user_id);
            
            // Redirect berdasarkan role
            switch ($user->role) {
                case 'admin':
                    return redirect()->intended(route('dashboard'))->with('success', 'Welcome back, Admin!');
                case 'leader':
                    return redirect()->intended(route('dashboard'))->with('success', 'Welcome back, Team Leader!');
                default:
                    return redirect()->intended(route('dashboard'))->with('success', 'Welcome to ProjectHub!');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
