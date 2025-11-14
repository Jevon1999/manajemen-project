{{-- User Sidebar Component --}}
@php
    // Determine user's project roles
    $projectRoles = DB::table('project_members')
        ->where('user_id', Auth::user()->user_id)
        ->pluck('role')
        ->toArray();
        
    $isDesigner = in_array('designer', $projectRoles);
    $isDeveloper = in_array('developer', $projectRoles);
    $isProjectManager = in_array('project_manager', $projectRoles);
    
    $userType = 'Team Member';
    $userIcon = '👤';
    $roleColor = 'bg-blue-100 text-blue-800';
    $gradientColors = 'from-blue-900 via-blue-800 to-blue-900';
    $borderColor = 'border-blue-700';
    $hoverColor = 'hover:bg-blue-700';
    
    if ($isProjectManager) {
        $userType = 'Project Manager';
        $userIcon = '🏆';
        $roleColor = 'bg-purple-100 text-purple-800';
        $gradientColors = 'from-purple-900 via-purple-800 to-purple-900';
        $borderColor = 'border-purple-700';
        $hoverColor = 'hover:bg-purple-700';
    } elseif ($isDesigner && $isDeveloper) {
        $userType = 'Designer & Developer';
        $userIcon = '🎨💻';
        $roleColor = 'bg-indigo-100 text-indigo-800';
        $gradientColors = 'from-indigo-900 via-indigo-800 to-indigo-900';
        $borderColor = 'border-indigo-700';
        $hoverColor = 'hover:bg-indigo-700';
    } elseif ($isDesigner) {
        $userType = 'UI/UX Designer';
        $userIcon = '🎨';
        $roleColor = 'bg-pink-100 text-pink-800';
        $gradientColors = 'from-pink-900 via-pink-800 to-pink-900';
        $borderColor = 'border-pink-700';
        $hoverColor = 'hover:bg-pink-700';
    } elseif ($isDeveloper) {
        $userType = 'Developer';
        $userIcon = '💻';
        $roleColor = 'bg-green-100 text-green-800';
        $gradientColors = 'from-green-900 via-green-800 to-green-900';
        $borderColor = 'border-green-700';
        $hoverColor = 'hover:bg-green-700';
    }
@endphp

<aside class="w-64 h-screen bg-gradient-to-b {{ $gradientColors }} text-white flex flex-col shadow-2xl rounded-r-2xl">
    <!-- Header Sidebar -->
    <div class="p-6 border-b {{ $borderColor }}">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                @if($isProjectManager)
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                @elseif($isDesigner)
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
                    </svg>
                @elseif($isDeveloper)
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                @endif
            </div>
            <div>
                <h1 class="font-bold text-lg">ProjectHub</h1>
                <p class="text-blue-300 text-sm">{{ $userIcon }} {{ $userType }}</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-6 py-4 border-b {{ $borderColor }}">
        <div class="flex items-center space-x-3">
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
            @else
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->full_name, 0, 1) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name }}</p>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $roleColor }}">
                    {{ $userIcon }} {{ $userType }}
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 {{ Request::routeIs('dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100' }}">
            <svg class="w-5 h-5 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Dashboard
        </a>

        <!-- User Work Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-blue-400 uppercase tracking-wider">
                {{ $userIcon }} {{ $userType }} Work
            </p>
            
            <!-- My Tasks -->
            <a href="{{ route('tasks.my') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 mt-2 {{ Request::routeIs('tasks.my') ? 'bg-blue-700 text-white' : 'text-blue-100' }}">
                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                My Tasks
            </a>

            <!-- Time Tracking -->
            <a href="{{ route('timelogs.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 {{ Request::routeIs('timelogs.*') ? 'bg-blue-700 text-white' : 'text-blue-100' }}">
                <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Time Logs
            </a>

            <!-- My Projects -->
            <a href="{{ route('projects.assigned') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 {{ Request::routeIs('projects.assigned') ? 'bg-blue-700 text-white' : 'text-blue-100' }}">
                <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                </svg>
                My Projects
            </a>

            @if($isProjectManager)
                <!-- Project Management (for users who are project managers) -->
                <a href="{{ route('projects.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 {{ Request::routeIs('projects.index') ? 'bg-purple-700 text-white' : 'text-purple-100' }}">
                    <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Manage Projects
                </a>
            @endif
        </div>

        <!-- Role-specific Tools -->
        @if($isDesigner || $isDeveloper)
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-blue-400 uppercase tracking-wider">
                🛠️ Professional Tools
            </p>
            
            @if($isDesigner)
                <!-- Design Resources -->
                <a href="#" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 mt-2 text-blue-100">
                    <svg class="w-5 h-5 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
                    </svg>
                    Design Assets
                </a>
            @endif
            
            @if($isDeveloper)
                <!-- Code Repositories -->
                <a href="#" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ $hoverColor }} transition-colors duration-200 {{ !$isDesigner ? 'mt-2' : '' }} text-blue-100">
                    <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Code Repository
                </a>
            @endif
        </div>
        @endif

        <!-- Common Menu -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-blue-400 uppercase tracking-wider">
                🌟 General
            </p>
            
            <!-- Calendar -->
            <a href="{{ route('calendar') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200 mt-2 text-blue-100">
                <svg class="w-5 h-5 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                </svg>
                Calendar
            </a>

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200 text-blue-100">
                <svg class="w-5 h-5 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17V7a4 4 0 00-8 0v10"></path>
                </svg>
                Notifications
                <span class="ml-auto bg-blue-600 text-white text-xs rounded-full px-2 py-1">0</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200 text-blue-100">
                <svg class="w-5 h-5 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Profile
            </a>
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t {{ $borderColor }}">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-blue-200 rounded-lg hover:bg-blue-600 hover:text-white transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
        </form>
    </div>
</aside>