{{-- Admin Sidebar Component --}}
<aside class="w-64 h-screen bg-gradient-to-b from-red-900 via-red-800 to-red-900 text-white flex flex-col shadow-2xl rounded-r-2xl">
    <!-- Header Sidebar -->
    <div class="p-6 border-b border-red-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-lg">ProjectHub</h1>
                <p class="text-red-300 text-sm">👑 System Admin</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-6 py-4 border-b border-red-700">
        <div class="flex items-center space-x-3">
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
            @else
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->full_name, 0, 1) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name }}</p>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    👑 System Administrator
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::routeIs('dashboard') ? 'bg-red-700 text-white' : 'text-red-100' }}">
            <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Dashboard
        </a>

        <!-- Admin Management Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-red-400 uppercase tracking-wider">
                👑 System Administration
            </p>
            
            <!-- Project Management -->
            <a href="{{ route('admin.projects.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 mt-2 {{ Request::routeIs('admin.projects.*') || Request::routeIs('projects.*') ? 'bg-red-700 text-white' : 'text-red-100' }}">
                <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2" />
                </svg>
                Project Management
            </a>
            
            <!-- User & Leader Management -->
            <a href="{{ route('users.management') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::routeIs('users.*') || Request::routeIs('admin.leaders.*') ? 'bg-red-700 text-white' : 'text-red-100' }}">
                <svg class="w-5 h-5 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                User & Leader Management
            </a>
            
            <!-- Task Management -->
            <a href="{{ route('admin.tasks.list') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::routeIs('admin.tasks.*') ? 'bg-red-700 text-white' : 'text-red-100' }}">
                <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Task Management
            </a>
            
            <!-- Reports Section with Submenu -->
            <div x-data="{ open: {{ Request::routeIs('admin.reports.*') ? 'true' : 'false' }} }">
                <!-- Main Reports Button -->
                <button @click="open = !open" class="group flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::routeIs('admin.reports.*') ? 'bg-red-700 text-white' : 'text-red-100' }}">
                    <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="flex-1 text-left">Reports</span>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <!-- Submenu -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-1 space-y-1 pl-11">
                    <a href="{{ route('admin.reports.export.index') }}" class="block px-4 py-2 text-sm rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::is('admin/reports/export') ? 'bg-red-700 text-white' : 'text-red-200' }}">
                        📥 Excel/CSV Export
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 text-sm rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::is('admin/reports') && !Request::has('type') ? 'bg-red-700 text-white' : 'text-red-200' }}">
                        📊 General Report
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'project']) }}" class="block px-4 py-2 text-sm rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::get('type') == 'project' ? 'bg-red-700 text-white' : 'text-red-200' }}">
                        📁 Per Project
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'monthly']) }}" class="block px-4 py-2 text-sm rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::get('type') == 'monthly' ? 'bg-red-700 text-white' : 'text-red-200' }}">
                        📅 Monthly Report
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'yearly']) }}" class="block px-4 py-2 text-sm rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::get('type') == 'yearly' ? 'bg-red-700 text-white' : 'text-red-200' }}">
                        📆 Yearly Report
                    </a>
                </div>
            </div>
            
            <!-- System Settings -->
            <a href="{{ route('admin.settings') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 {{ Request::routeIs('admin.settings*') ? 'bg-red-700 text-white' : 'text-red-100' }}">
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                System Settings
            </a>
        </div>

        <!-- Common Menu -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-red-400 uppercase tracking-wider">
                🌟 General
            </p>
            
            <!-- Calendar -->
            <a href="{{ route('calendar') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-600 transition-colors duration-200 mt-2 text-red-100">
                <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                </svg>
                Calendar
            </a>

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-600 transition-colors duration-200 text-red-100">
                <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17V7a4 4 0 00-8 0v10"></path>
                </svg>
                Notifications
                <span class="ml-auto bg-red-600 text-white text-xs rounded-full px-2 py-1">{{ 0 }}</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-red-600 transition-colors duration-200 text-red-100">
                <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                My Profile
            </a>
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-red-700">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-200 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>