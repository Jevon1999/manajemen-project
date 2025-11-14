<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'ProjectHub') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- AOS Animation CDN -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

        <!-- Vue 3 CDN -->
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        
        <!-- Inertia.js CDN -->
        <script src="https://unpkg.com/@inertiajs/vue3@1.0.14/dist/index.umd.js"></script>

        <!-- Heroicons CDN -->
        <script src="https://unpkg.com/@heroicons/vue@2.0.18/24/outline/index.js" type="module"></script>

        <!-- Custom Styles -->
        <style>
            [x-cloak] { display: none !important; }
            .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
            .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
            .from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-to: rgb(37 99 235 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
            .to-purple-600 { --tw-gradient-to: #9333ea; }
            .to-indigo-800 { --tw-gradient-to: #3730a3; }
            .from-yellow-400 { --tw-gradient-from: #facc15; --tw-gradient-to: rgb(250 204 21 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
            .to-orange-500 { --tw-gradient-to: #f97316; }
            .bg-clip-text { -webkit-background-clip: text; background-clip: text; }
            .text-transparent { color: transparent; }
            .backdrop-blur-lg { backdrop-filter: blur(16px); }
            .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
        </style>

        <!-- Route Helper -->
        <script>
            window.route = function(name, params) {
                const routes = {
                    'login': '/',
                    'login.post': '/login',
                    'register': '/register',
                    'register.post': '/register',
                    'dashboard': '/dashboard'
                };
                return routes[name] || '/';
            };
        </script>

        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-50">
        @inertia

        <!-- Initialize everything -->
        <script>
            // Initialize AOS
            document.addEventListener('DOMContentLoaded', function() {
                AOS.init({
                    duration: 800,
                    once: true,
                });
            });

            // Initialize Inertia App
            const { createApp, h } = Vue;
            
            createApp({
                render: () => h(App, props)
            }).mount('#app');
        </script>
    </body>
</html>
