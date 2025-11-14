<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProjectHub - Login</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- AOS Animation CDN -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Custom Styles -->
    <style>
        body { font-family: 'Figtree', sans-serif; }
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
        .from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-to: rgb(37 99 235 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .via-purple-600 { --tw-gradient-to: rgb(147 51 234 / 0); --tw-gradient-stops: var(--tw-gradient-from), #9333ea, var(--tw-gradient-to); }
        .to-indigo-800 { --tw-gradient-to: #3730a3; }
        .from-yellow-400 { --tw-gradient-from: #facc15; --tw-gradient-to: rgb(250 204 21 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .to-orange-500 { --tw-gradient-to: #f97316; }
        .bg-clip-text { -webkit-background-clip: text; background-clip: text; }
        .text-transparent { color: transparent; }
        .backdrop-blur-lg { backdrop-filter: blur(16px); }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left side - Form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <!-- Logo and Header -->
                <div data-aos="fade-down" data-aos-delay="100">
                    <div class="flex items-center justify-center lg:justify-start mb-8">
                        <div class="h-12 w-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <span class="ml-3 text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            ProjectHub
                        </span>
                    </div>
                    
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h2>
                        <p class="text-gray-600">Sign in to your account to continue</p>
                    </div>
                </div>

                <!-- Form -->
                <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Enter your email"
                                />
                            </div>
                            @error('email')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Enter your password"
                                />
                                <button
                                    type="button"
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg id="eyeIcon" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me and Forgot Password -->
                        <div class="flex items-center justify-between" data-aos="fade-up" data-aos-delay="300">
                            <div class="flex items-center">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors"
                                />
                                <label for="remember" class="ml-2 block text-sm text-gray-700">
                                    Remember me
                                </label>
                            </div>

                            <div class="text-sm">
                                <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                    Forgot your password?
                                </a>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div data-aos="fade-up" data-aos-delay="400">
                            <button
                                type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                            >
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Sign in
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Social Login Divider -->
                    <div class="mt-6" data-aos="fade-up" data-aos-delay="450">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">Or continue with</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Login Buttons -->
                    <div class="mt-6 grid grid-cols-2 gap-3" data-aos="fade-up" data-aos-delay="400">
                        <!-- Google Login -->
                        <a href="{{ route('social.redirect', 'google') }}" 
                           class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-200 hover:scale-105">
                            <svg class="h-5 w-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="ml-2">Google</span>
                        </a>

                        <!-- GitHub Login -->
                        <a href="{{ route('social.redirect', 'github') }}" 
                           class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-200 hover:scale-105">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-2">GitHub</span>
                        </a>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-6 text-center" data-aos="fade-up" data-aos-delay="500">
                        <p class="text-sm text-gray-600">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500 transition-colors">
                                Create one here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Image/Illustration -->
        <div class="hidden lg:block relative w-0 flex-1">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800">
                <div class="absolute inset-0 bg-black opacity-20"></div>
                
                <!-- Decorative Elements -->
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Animated Background Elements -->
                    <div class="absolute top-20 left-10 w-32 h-32 bg-white bg-opacity-5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1000"></div>
                    <div class="absolute bottom-32 right-20 w-24 h-24 bg-white bg-opacity-5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1200"></div>
                    <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white bg-opacity-5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1400"></div>

                    <!-- Project Cards -->
                    <div 
                        class="absolute top-20 right-10 w-64 h-40 bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl border border-white border-opacity-20 p-6"
                        data-aos="fade-left" 
                        data-aos-delay="600"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg"></div>
                            <div class="ml-3">
                                <div class="w-20 h-3 bg-white bg-opacity-30 rounded"></div>
                                <div class="w-16 h-2 bg-white bg-opacity-20 rounded mt-2"></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="w-full h-2 bg-white bg-opacity-20 rounded"></div>
                            <div class="w-3/4 h-2 bg-white bg-opacity-20 rounded"></div>
                            <div class="w-1/2 h-2 bg-white bg-opacity-20 rounded"></div>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 bg-white bg-opacity-30 rounded-full border border-white border-opacity-20"></div>
                                <div class="w-6 h-6 bg-white bg-opacity-30 rounded-full border border-white border-opacity-20"></div>
                                <div class="w-6 h-6 bg-white bg-opacity-30 rounded-full border border-white border-opacity-20"></div>
                            </div>
                            <div class="w-12 h-6 bg-green-400 bg-opacity-30 rounded-full"></div>
                        </div>
                    </div>

                    <div 
                        class="absolute bottom-40 left-10 w-56 h-36 bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl border border-white border-opacity-20 p-5"
                        data-aos="fade-right" 
                        data-aos-delay="800"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl"></div>
                            <div class="text-xs text-white text-opacity-70">85% Complete</div>
                        </div>
                        <div class="space-y-3">
                            <div class="w-full h-2 bg-white bg-opacity-20 rounded-full">
                                <div class="w-4/5 h-2 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full"></div>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <div class="h-6 bg-white bg-opacity-20 rounded"></div>
                                <div class="h-6 bg-white bg-opacity-20 rounded"></div>
                                <div class="h-6 bg-purple-400 bg-opacity-50 rounded"></div>
                                <div class="h-6 bg-white bg-opacity-10 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="relative h-full flex flex-col justify-center items-center text-center p-12">
                    <div data-aos="zoom-in" data-aos-delay="400">
                        <h1 class="text-4xl font-bold text-white mb-6">
                            Manage Projects
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                                Like Never Before
                            </span>
                        </h1>
                        <p class="text-xl text-blue-100 mb-8 max-w-md">
                            Experience the power of seamless project management with our intuitive and modern platform.
                        </p>
                        
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-8 text-center max-w-md" data-aos="fade-up" data-aos-delay="600">
                            <div>
                                <div class="text-2xl font-bold text-white">1000+</div>
                                <div class="text-sm text-blue-200">Projects</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">50+</div>
                                <div class="text-sm text-blue-200">Teams</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white">99%</div>
                                <div class="text-sm text-blue-200">Uptime</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize AOS
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
            });
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>
