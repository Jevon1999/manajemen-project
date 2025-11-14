@extends('layout.app')

@section('title', 'User Details')
@section('page-title', 'User Details')
@section('page-description', 'Detail informasi pengguna dan aktivitas')

@section('content')
<div class="space-y-6">
    <!-- User Profile Card -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">User Profile</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('admin.users') }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-start space-x-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($user->avatar)
                        <img class="h-20 w-20 rounded-full" src="{{ $user->avatar }}" alt="{{ $user->full_name }}">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-xl font-medium text-gray-700">{{ substr($user->full_name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                
                <!-- User Info -->
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                    <p class="text-sm text-gray-600">@{{ $user->username }}</p>
                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    
                    <div class="mt-4 flex items-center space-x-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($user->role === 'admin') bg-red-100 text-red-800 
                            @elseif($user->role === 'leader') bg-blue-100 text-blue-800 
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($user->status === 'active') bg-green-100 text-green-800 
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Joined</dt>
                            <dd class="mt-1 text-gray-900">{{ $user->created_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-gray-900">{{ $user->updated_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Total Time Logged</dt>
                            <dd class="mt-1 text-gray-900">{{ number_format($timeLogs, 1) }} hours</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects & Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Created Projects -->
        <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Created Projects</h3>
                <p class="text-sm text-gray-600">Projects created by this user</p>
            </div>
            <div class="p-6">
                @forelse($projects as $project)
                <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ $project->project_name }}</h4>
                        <p class="text-xs text-gray-500">{{ $project->description }}</p>
                        <p class="text-xs text-gray-400">Created {{ $project->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($project->status === 'active') bg-green-100 text-green-800 
                            @elseif($project->status === 'completed') bg-blue-100 text-blue-800 
                            @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800 
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No projects created</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Project Memberships -->
        <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Project Memberships</h3>
                <p class="text-sm text-gray-600">Projects where this user is a member</p>
            </div>
            <div class="p-6">
                @forelse($memberships as $membership)
                @if($membership->project)
                <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ $membership->project->project_name }}</h4>
                        <p class="text-xs text-gray-500">{{ $membership->project->description }}</p>
                        <p class="text-xs text-gray-400">Joined {{ $membership->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($membership->role === 'leader') bg-blue-100 text-blue-800 
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($membership->role) }}
                        </span>
                    </div>
                </div>
                @endif
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No project memberships</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Social Login Connections -->
    @if($user->google_id || $user->github_id)
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="300">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Social Login Connections</h3>
            <p class="text-sm text-gray-600">Connected social media accounts</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($user->google_id)
                <div class="flex items-center p-3 bg-red-50 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Google</p>
                        <p class="text-xs text-gray-500">ID: {{ $user->google_id }}</p>
                    </div>
                </div>
                @endif

                @if($user->github_id)
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">GitHub</p>
                        <p class="text-xs text-gray-500">ID: {{ $user->github_id }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection