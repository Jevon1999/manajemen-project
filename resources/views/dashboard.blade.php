<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ProjectHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- sidebar toggle removed: sidebar always visible -->
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Include Role-Based Sidebar -->
        @include('layout.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-0">
            <!-- Mobile Header -->
            <div class="lg:hidden bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="text-gray-500">&nbsp;</div>
                    <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
                    <div></div>
                </div>
            </div>

            <!-- Content Area -->
            <main id="main-content" class="flex-1 p-3 sm:p-4 md:p-6 pb-24 sm:pb-28">
                <!-- Leader Dashboard Redirect -->
                @if(Auth::user()->role === 'leader')
                    <script>
                        window.location.href = '{{ route("leader.dashboard") }}';
                    </script>
                    <div class="text-center py-8">
                        <div class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Redirecting to Leader Dashboard...
                        </div>
                    </div>
                @else
                    <!-- Welcome Message -->
                    @if(session('success'))
                        <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded-lg text-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                <!-- Page Header -->
                <div class="mb-4 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm sm:text-base text-gray-600 truncate">Welcome back, {{ Auth::user()->full_name }}!</p>
                </div>

                <!-- Dashboard Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
                    <!-- Welcome Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-circle text-blue-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Welcome Back</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900 truncate">{{ Auth::user()->full_name }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3">
                            <div class="text-xs sm:text-sm">
                                <a href="{{ route('profile') }}" class="font-medium text-blue-700 hover:text-blue-900">
                                    View profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Card -->
                    @if(Auth::user()->role !== 'user')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-project-diagram text-green-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Projects</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900 truncate">{{ Auth::user()->role === 'admin' ? 'All Projects' : 'My Projects' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3">
                            <div class="text-xs sm:text-sm">
                                <a href="{{ route('projects.index') }}" class="font-medium text-green-700 hover:text-green-900">
                                    View projects
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tasks Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tasks text-purple-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Tasks</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900 truncate">{{ Auth::user()->role === 'user' ? 'My Tasks' : 'Team Tasks' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3">
                            <div class="text-xs sm:text-sm">
                                <a href="{{ route('cards.index') }}" class="font-medium text-purple-700 hover:text-purple-900">
                                    View tasks
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Role-specific Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    @if(Auth::user()->role === 'admin')
                                        <i class="fas fa-users-cog text-red-500 text-xl sm:text-2xl"></i>
                                    @elseif(Auth::user()->role === 'leader')
                                        <i class="fas fa-users text-yellow-500 text-xl sm:text-2xl"></i>
                                    @else
                                        <i class="fas fa-clock text-indigo-500 text-xl sm:text-2xl"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">
                                            @if(Auth::user()->role === 'admin')
                                                User Management
                                            @elseif(Auth::user()->role === 'leader')
                                                Team Management
                                            @else
                                                Time Tracking
                                            @endif
                                        </dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900 truncate">
                                            @if(Auth::user()->role === 'admin')
                                                Manage System
                                            @elseif(Auth::user()->role === 'leader')
                                                Manage Team
                                            @else
                                                Track Work
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3">
                            <div class="text-xs sm:text-sm">
                                @if(Auth::user()->role === 'admin')
                                    <a href="{{ route('users.index') }}" class="font-medium text-red-700 hover:text-red-900">
                                        Manage users
                                    </a>
                                @elseif(Auth::user()->role === 'leader')
                                    <a href="{{ route('admin.projects.admin.manage-team-members') }}" class="font-medium text-yellow-700 hover:text-yellow-900">
                                        View team
                                    </a>
                                @else
                                    <a href="{{ route('timelogs.index') }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                                        Track time
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role-Based Quick Actions -->
                <div class="bg-white shadow rounded-lg mb-6 sm:mb-8">
                    <div class="px-3 py-3 sm:px-4 sm:py-3.5 md:px-6 md:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-3 sm:p-4 md:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            @if(Auth::user()->role === 'admin')
                                <!-- Admin Quick Actions -->
                                <a href="{{ route('projects.create') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-plus-circle text-blue-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">Create Project</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Start a new project</p>
                                    </div>
                                </a>
                                <a href="{{ route('users.index') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    <i class="fas fa-user-plus text-green-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">Manage Users</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Add or edit users</p>
                                    </div>
                                </a>
                                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-chart-bar text-purple-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">View Reports</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">System analytics</p>
                                    </div>
                                </a>
                            @elseif(Auth::user()->role === 'leader')
                                <!-- Team Leader Quick Actions -->
                                <a href="{{ route('cards.create') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-plus text-blue-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">Create Task</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Add new task</p>
                                    </div>
                                </a>
                                <a href="{{ route('admin.projects.admin.manage-team-members') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    <i class="fas fa-users text-green-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">Team Members</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">View your team</p>
                                    </div>
                                </a>
                                <a href="{{ route('boards.index') }}" class="flex items-center gap-3 p-3 sm:p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-columns text-purple-600 text-lg sm:text-xl flex-shrink-0"></i>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 truncate">Project Boards</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Kanban view</p>
                                    </div>
                                </a>
                            @else
                                <!-- User Quick Actions - Removed -->
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-3 py-3 sm:px-4 sm:py-3.5 md:px-6 md:py-4 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="p-3 sm:p-4 md:p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex gap-2 sm:gap-3">
                                            <div class="flex-shrink-0">
                                                <span class="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-blue-500 flex items-center justify-center ring-4 sm:ring-8 ring-white">
                                                    <i class="fas fa-user text-white text-[10px] sm:text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-0.5 sm:pt-1.5 flex flex-col sm:flex-row justify-between gap-1 sm:gap-4">
                                                <div class="min-w-0">
                                                    <p class="text-xs sm:text-sm text-gray-500 truncate">Welcome to <span class="font-medium text-gray-900">ProjectHub</span></p>
                                                </div>
                                                <div class="text-left sm:text-right text-xs sm:text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ now() }}">Now</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Account setup <span class="font-medium text-gray-900">completed</span></p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ Auth::user()->created_at }}">{{ Auth::user()->created_at->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </main>
        </div>
    </div>
</body>
</html>
