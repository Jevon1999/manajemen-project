<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    
    <title>{{ config('app.name', 'Project Management') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar -->
        <div x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             class="fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            @include('layout.sidebar')
        </div>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden w-full">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-30">
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Mobile Menu Button + Page Title -->
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Hamburger Button (Mobile Only) -->
                            <button @click="sidebarOpen = !sidebarOpen"
                                    class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                                </svg>
                            </button>
                            
                            <!-- Page Title -->
                            <div class="flex-1 min-w-0">
                                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 truncate">
                                    @yield('page-title', 'Dashboard')
                                </h1>
                                <p class="text-xs sm:text-sm text-gray-600 mt-1 truncate hidden sm:block">
                                    @yield('page-description', 'Selamat datang di sistem manajemen proyek')
                                </p>
                            </div>
                        </div>
                        
                        <!-- Header Actions -->
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <!-- Search (Hidden on mobile, show on tablet+) -->
                            <div class="relative hidden md:block">
                                <input type="text" placeholder="Cari..." 
                                       class="w-48 lg:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Search Icon (Mobile Only) -->
                            <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                            
                            <!-- Notifications Bell -->
                            @include('components.notification-bell')
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-4 sm:p-6">
                    <!-- Breadcrumb -->
                    @if(isset($breadcrumbs))
                    <nav class="flex mb-4 sm:mb-6 overflow-x-auto" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            @foreach($breadcrumbs as $index => $breadcrumb)
                                <li class="inline-flex items-center">
                                    @if($index > 0)
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                    @if($loop->last)
                                        <span class="text-gray-500 text-sm font-medium">{{ $breadcrumb['title'] }}</span>
                                    @else
                                        <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                    @endif
                    
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 sm:mb-6" role="alert" data-aos="fade-down">
                            <div class="flex items-start">
                                <div class="py-1 flex-shrink-0">
                                    <svg class="fill-current h-5 w-5 sm:h-6 sm:w-6 text-green-500 mr-3 sm:mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm sm:text-base">Berhasil!</p>
                                    <p class="text-xs sm:text-sm break-words">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 sm:mb-6" role="alert" data-aos="fade-down">
                            <div class="flex items-start">
                                <div class="py-1 flex-shrink-0">
                                    <svg class="fill-current h-5 w-5 sm:h-6 sm:w-6 text-red-500 mr-3 sm:mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm sm:text-base">Error!</p>
                                    <p class="text-xs sm:text-sm break-words">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Page Content -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Vue.js 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- AOS JavaScript -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
    
    <!-- Real-time Notifications -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    <!-- Floating Action Button (mobile/tablet) - Optional Quick Action -->
    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'leader')
    <div class="fixed bottom-6 right-6 z-30 lg:hidden">
        <button onclick="window.location.href='{{ auth()->user()->role == 'admin' ? route('admin.projects.create') : route('leader.projects') }}'"
                class="bg-blue-600 text-white p-4 rounded-full shadow-2xl hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 transform hover:scale-110" 
                title="Quick Action">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>
    @endif
    
    <!-- Close sidebar when clicking outside (mobile) -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Sidebar will auto-close when route changes on mobile
            if (window.innerWidth < 1024) {
                window.addEventListener('beforeunload', () => {
                    Alpine.store('sidebarOpen', false);
                });
            }
        });
    </script>
    
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
