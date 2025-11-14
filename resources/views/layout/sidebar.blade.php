{{-- Simplified Role-Based Sidebar --}}
<aside class="w-64 h-full bg-gradient-to-b from-purple-900 via-indigo-900 to-purple-800 text-white flex flex-col shadow-2xl lg:rounded-r-2xl overflow-y-auto">
    <!-- Header Sidebar -->
    <div class="p-4 sm:p-6 border-b border-gray-700 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-lg">ProjectHub</h1>
                <p class="text-gray-400 text-sm">{{ ucfirst(Auth::user()->role ?? 'user') }} Panel</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-700 flex-shrink-0">
        <div class="flex items-center space-x-2 sm:space-x-3">
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
            @else
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->full_name, 0, 1) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name }}</p>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    @if(auth()->user()->role == 'admin')
                        👑 Admin
                    @elseif(auth()->user()->role == 'leader')
                        👨‍💼 Leader  
                    @elseif(auth()->user()->role == 'developer')
                        💻 Developer
                    @elseif(auth()->user()->role == 'designer')
                        🎨 Designer
                    @else
                        👤 User
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 sm:px-4 py-4 sm:py-6 space-y-1 overflow-y-auto">
        <!-- Dashboard (Common for all roles) -->
        <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 {{ Request::routeIs('dashboard') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Dashboard
        </a>

        <!-- ADMIN ONLY MENUS -->
        @if(auth()->user()->role == 'admin')
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-red-400 uppercase tracking-wider">
                👑 Administrator
            </p>
            
            <a href="{{ route('admin.projects.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 mt-2 {{ request()->is('admin/projects*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2" />
                </svg>
                Project Management
            </a>
        </div>
        @endif

        @if(auth()->user()->role == 'admin')
        <a href="{{ route('users.management') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->is('users*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            User Management
        </a>
        @endif

        @if(auth()->user()->role == 'admin')
        <!-- Reports Section with Submenu -->
        <div x-data="{ open: {{ Request::routeIs('admin.reports.*') ? 'true' : 'false' }} }">
            <!-- Main Reports Button -->
            <button @click="open = !open" class="group flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 {{ Request::routeIs('admin.reports.*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="flex-1 text-left">Reports</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            
            <!-- Submenu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0 -translate-y-1" 
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mt-2 space-y-1 bg-purple-900 bg-opacity-50 rounded-lg p-2 ml-4">
                <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2 text-xs font-medium rounded-md transition-all duration-200 {{ Request::is('admin/reports') && !Request::has('type') ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-300 hover:bg-purple-700 hover:text-white hover:pl-4' }}">
                    <span class="mr-2">📊</span>
                    <span>General Report</span>
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'project']) }}" class="flex items-center px-3 py-2 text-xs font-medium rounded-md transition-all duration-200 {{ Request::get('type') == 'project' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-300 hover:bg-purple-700 hover:text-white hover:pl-4' }}">
                    <span class="mr-2">📁</span>
                    <span>Per Project</span>
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'monthly']) }}" class="flex items-center px-3 py-2 text-xs font-medium rounded-md transition-all duration-200 {{ Request::get('type') == 'monthly' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-300 hover:bg-purple-700 hover:text-white hover:pl-4' }}">
                    <span class="mr-2">📅</span>
                    <span>Monthly Report</span>
                </a>
                <a href="{{ route('admin.reports.index', ['type' => 'yearly']) }}" class="flex items-center px-3 py-2 text-xs font-medium rounded-md transition-all duration-200 {{ Request::get('type') == 'yearly' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-300 hover:bg-purple-700 hover:text-white hover:pl-4' }}">
                    <span class="mr-2">📆</span>
                    <span>Yearly Report</span>
                </a>
            </div>
        </div>
        @endif

        @if(auth()->user()->role == 'admin')
        <a href="{{ route('admin.settings') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->is('admin/settings*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>
        @endif

        <!-- LEADER MENUS -->
        @if(auth()->user()->role == 'leader')
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-400 uppercase tracking-wider">
                👨‍💼 Team Leader
            </p>
            
            <!-- My Projects (includes Board & Cards) -->
            <a href="{{ route('leader.projects') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 mt-2 {{ request()->routeIs('leader.projects*') || request()->routeIs('leader.boards.*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                </svg>
                My Projects
            </a>
        </div>
        @endif

        <!-- USER MENUS -->
        @if(auth()->user()->role == 'user')
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                👤 Team Member
            </p>
            
            <a href="{{ route('tasks.my') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 mt-2 {{ request()->routeIs('tasks.my') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                My Tasks
            </a>
        </div>
        @endif

        <!-- Common Menu for All Roles -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                🌟 General
            </p>
            
            <!-- Leaderboard -->
            <a href="{{ route('leaderboard.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 mt-2 {{ Request::routeIs('leaderboard.*') ? 'bg-purple-700 text-white' : 'text-gray-300 hover:bg-purple-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                🏆 Leaderboard
            </a>

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-300 hover:bg-purple-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17V7a4 4 0 00-8 0v10"></path>
                </svg>
                Notifications
                <span class="notification-badge ml-auto bg-red-600 text-white text-xs rounded-full px-2 py-1 hidden">0</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-300 hover:bg-purple-700 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Profile
            </a>
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-3 sm:p-4 border-t border-gray-700 flex-shrink-0">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-300 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>