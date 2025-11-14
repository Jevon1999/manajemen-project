<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use RespondsWithJson;
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'same:password'],
            'role' => ['nullable', 'in:admin,leader,user'],
        ]);

        // Create new user
        $user = User::create([
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'user',
            'status' => 'active',
        ]);

        // Generate token
        $token = $user->createToken('flutter')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve($request),
        ], 'Registration successful');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required','string'], // email or username
            'password' => ['required','string'],
            'device_name' => ['nullable','string'],
        ]);

        $user = User::where('email', $data['login'])
            ->orWhere('username', $data['login'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('Kredensial tidak valid', 422, ['login' => ['Email/username atau password salah']]);
        }

        $token = $user->createToken($data['device_name'] ?? 'flutter')->plainTextToken;
        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve($request),
        ], 'Login success');
    }

    public function me(Request $request)
    {
        return $this->successResource(new UserResource($request->user()), 'Profile');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return $this->success(null, 'Logged out');
    }
}
