{{-- Team Leader Sidebar Component --}}
<aside class="w-64 h-screen bg-gradient-to-b from-indigo-900 via-purple-900 to-indigo-800 text-white flex flex-col shadow-2xl rounded-r-2xl">
    <!-- Header Sidebar -->
    <div class="p-6 border-b border-indigo-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-lg">ProjectHub</h1>
                <p class="text-indigo-300 text-sm">👨‍💼 Team Leader</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-6 py-4 border-b border-indigo-700">
        <div class="flex items-center space-x-3">
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
            @else
                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->full_name, 0, 1) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name }}</p>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    👨‍💼 Team Leader
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('leader.dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 {{ Request::routeIs('leader.dashboard*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
            <svg class="w-5 h-5 mr-3 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 012-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2z"/>
            </svg>
            Leader Dashboard
        </a>

        <!-- Core Leader Functions -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-400 uppercase tracking-wider">
                👨‍💼 Core Leadership Functions
            </p>
            
            <!-- Quick Access to Dashboard Sections -->
            <a href="{{ route('leader.dashboard') }}#assign-tasks" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 mt-2 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Assign Tasks
            </a>
            
            <a href="{{ route('leader.dashboard') }}#set-priority" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Set Priority
            </a>
            
            <a href="{{ route('leader.dashboard') }}#update-status" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Update Status
            </a>
            
            <a href="{{ route('leader.dashboard') }}#view-progress" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 002 2h2a2 2 0 012-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2z"/>
                </svg>
                View Progress
            </a>
        </div>

        <!-- Project Management -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">
                📁 Project Management
            </p>
            
            <!-- My Projects -->
            <a href="{{ route('leader.projects') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 mt-2 {{ Request::routeIs('leader.projects*') && !Request::routeIs('leader.dashboard*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
                <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                My Projects
            </a>
            
            <!-- Project Boards -->
            <a href="{{ route('boards.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 {{ Request::routeIs('boards.*') ? 'bg-indigo-700 text-white' : 'text-indigo-100' }}">
                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2"/>
                </svg>
                Project Boards
            </a>
        </div>

        <!-- Common Menu -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">
                🌟 General
            </p>
            
            <!-- Calendar -->
            <a href="{{ route('calendar') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-600 transition-colors duration-200 mt-2 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                </svg>
                Calendar
            </a>

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-600 transition-colors duration-200 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17V7a4 4 0 00-8 0v10"></path>
                </svg>
                Notifications
                <span class="ml-auto bg-indigo-600 text-white text-xs rounded-full px-2 py-1">{{ 0 }}</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-600 transition-colors duration-200 text-indigo-100">
                <svg class="w-5 h-5 mr-3 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Profile
            </a>
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-indigo-700">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-indigo-200 rounded-lg hover:bg-indigo-600 hover:text-white transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>