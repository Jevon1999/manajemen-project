<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Leader Dashboard - ProjectHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Include Leader Sidebar -->
        @include('leader.partials.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Leader Dashboard</h1>
                    <div class="text-sm text-gray-500">
                        {{ now()->format('l, F j, Y') }}
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 p-3 sm:p-4 md:p-6 pb-24 sm:pb-28" x-data="leaderDashboard()">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
                    <!-- My Projects -->
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-project-diagram text-blue-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">My Projects</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900" x-text="stats.projects"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tasks -->
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tasks text-green-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900" x-text="stats.total_tasks"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-yellow-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Pending Tasks</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900" x-text="stats.pending_tasks"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tasks -->
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-purple-500 text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Completed</dt>
                                        <dd class="text-base sm:text-lg font-medium text-gray-900" x-text="stats.completed_tasks"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Projects Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
                    <!-- Total Completed -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-flag-checkered text-white text-2xl sm:text-3xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-indigo-100 truncate">Completed Projects</dt>
                                        <dd class="text-xl sm:text-2xl font-bold text-white" x-text="stats.completed_projects"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- On Time -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-double text-white text-2xl sm:text-3xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-green-100 truncate">On Time</dt>
                                        <dd class="text-xl sm:text-2xl font-bold text-white" x-text="stats.completed_on_time"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Late Completion -->
                    <div class="bg-gradient-to-br from-red-500 to-red-600 overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-white text-2xl sm:text-3xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-red-100 truncate">Late Completion</dt>
                                        <dd class="text-xl sm:text-2xl font-bold text-white" x-text="stats.completed_late"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Projects List -->
                <section class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-trophy mr-2 text-yellow-500"></i>
                            Recently Completed Projects
                        </h3>
                        <a href="{{ route('leader.projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                            View All Projects →
                        </a>
                    </div>
                    <div class="p-6">
                        <template x-if="completedProjects && completedProjects.length > 0">
                            <div class="space-y-3">
                                <template x-for="project in completedProjects" :key="project.project_id">
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <h4 class="font-medium text-gray-900" x-text="project.project_name"></h4>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :class="{
                                                          'bg-green-100 text-green-800': project.badge_color === 'green',
                                                          'bg-yellow-100 text-yellow-800': project.badge_color === 'yellow',
                                                          'bg-red-100 text-red-800': project.badge_color === 'red'
                                                      }"
                                                      x-text="project.delay_message">
                                                </span>
                                            </div>
                                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                                <span>
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    Deadline: <span x-text="project.deadline"></span>
                                                </span>
                                                <span>
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Completed: <span x-text="project.completed_at"></span>
                                                </span>
                                                <template x-if="project.delay_days > 0">
                                                    <span class="text-red-600 font-medium">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <span x-text="project.delay_days"></span> days late
                                                    </span>
                                                </template>
                                            </div>
                                            <template x-if="project.completion_notes">
                                                <p class="mt-2 text-sm text-gray-600" x-text="project.completion_notes"></p>
                                            </template>
                                        </div>
                                        <a :href="`/leader/projects/${project.project_id}`" 
                                           class="ml-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-eye mr-2"></i>
                                            View
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!completedProjects || completedProjects.length === 0">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No completed projects yet</p>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- Core Functions Sections -->
                <div class="space-y-8">
                    
                    <!-- 1. Assign Tasks Section -->
                    <section id="assign-tasks" class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-user-plus mr-2 text-green-500"></i>
                                Assign Tasks
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Quick Task Assignment -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Quick Assignment</h4>
                                    <div class="space-y-3">
                                        <select x-model="assignment.project_id" @change="loadProjectTeam()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Project</option>
                                            <template x-for="project in projects" :key="project.project_id">
                                                <option :value="project.project_id" x-text="project.project_name"></option>
                                            </template>
                                        </select>
                                        
                                        <input type="text" x-model="assignment.task_title" placeholder="Task Title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        
                                        <select x-model="assignment.assignee" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Assign to Team Member</option>
                                            <template x-for="member in teamMembers" :key="member.user_id">
                                                <option :value="member.user_id" x-text="member.full_name + ' (' + member.role + ')'"></option>
                                            </template>
                                        </select>
                                        
                                        <button @click="quickAssignTask()" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <i class="fas fa-plus mr-2"></i>
                                            Quick Assign
                                        </button>
                                    </div>
                                </div>

                                <!-- Recent Assignments -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Recent Assignments</h4>
                                    <div class="space-y-2 max-h-48 overflow-y-auto">
                                        <template x-for="task in recentTasks" :key="task.card_id">
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900" x-text="task.card_title"></p>
                                                    <p class="text-xs text-gray-500" x-text="'Assigned to: ' + task.assignee_name"></p>
                                                </div>
                                                <span class="text-xs text-gray-400" x-text="task.created_at"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 2. Set Priority Section -->
                    <section id="set-priority" class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                                Set Priority
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Priority Filter -->
                                <div class="flex space-x-2">
                                    <button @click="priorityFilter = 'all'" :class="priorityFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">All</button>
                                    <button @click="priorityFilter = 'critical'" :class="priorityFilter === 'critical' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">Critical</button>
                                    <button @click="priorityFilter = 'high'" :class="priorityFilter === 'high' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">High</button>
                                    <button @click="priorityFilter = 'medium'" :class="priorityFilter === 'medium' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">Medium</button>
                                    <button @click="priorityFilter = 'low'" :class="priorityFilter === 'low' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">Low</button>
                                </div>

                                <!-- Tasks List for Priority Setting -->
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    <template x-for="task in filteredTasks" :key="task.card_id">
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <h5 class="font-medium text-gray-900" x-text="task.card_title"></h5>
                                                <p class="text-sm text-gray-500" x-text="task.project_name"></p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <select @change="updateTaskPriority(task.card_id, $event.target.value)" class="text-sm border border-gray-300 rounded-md px-2 py-1">
                                                    <option value="low" :selected="task.priority === 'low'">Low</option>
                                                    <option value="medium" :selected="task.priority === 'medium'">Medium</option>
                                                    <option value="high" :selected="task.priority === 'high'">High</option>
                                                    <option value="critical" :selected="task.priority === 'critical'">Critical</option>
                                                </select>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                                      :class="{
                                                          'bg-red-100 text-red-800': task.priority === 'critical',
                                                          'bg-orange-100 text-orange-800': task.priority === 'high',
                                                          'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                                                          'bg-green-100 text-green-800': task.priority === 'low'
                                                      }" 
                                                      x-text="task.priority.charAt(0).toUpperCase() + task.priority.slice(1)">
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 3. Update Status Section -->
                    <section id="update-status" class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-tasks mr-2 text-blue-500"></i>
                                Update Status
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Status Filter -->
                                <div class="flex space-x-2">
                                    <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">All</button>
                                    <button @click="statusFilter = 'todo'" :class="statusFilter === 'todo' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">To Do</button>
                                    <button @click="statusFilter = 'in_progress'" :class="statusFilter === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">In Progress</button>
                                    <button @click="statusFilter = 'review'" :class="statusFilter === 'review' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">Review</button>
                                    <button @click="statusFilter = 'done'" :class="statusFilter === 'done' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded-md text-sm font-medium">Done</button>
                                </div>

                                <!-- Tasks List for Status Update -->
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    <template x-for="task in filteredTasksByStatus" :key="task.card_id">
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <h5 class="font-medium text-gray-900" x-text="task.card_title"></h5>
                                                <p class="text-sm text-gray-500" x-text="task.assignee_name"></p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <select @change="updateTaskStatus(task.card_id, $event.target.value)" class="text-sm border border-gray-300 rounded-md px-2 py-1">
                                                    <option value="todo" :selected="task.status === 'todo'">To Do</option>
                                                    <option value="in_progress" :selected="task.status === 'in_progress'">In Progress</option>
                                                    <option value="review" :selected="task.status === 'review'">Review</option>
                                                    <option value="done" :selected="task.status === 'done'">Done</option>
                                                </select>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                                      :class="{
                                                          'bg-gray-100 text-gray-800': task.status === 'todo',
                                                          'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                                          'bg-yellow-100 text-yellow-800': task.status === 'review',
                                                          'bg-green-100 text-green-800': task.status === 'done'
                                                      }" 
                                                      x-text="task.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())">
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 4. View Progress Section -->
                    <section id="view-progress" class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <i class="fas fa-chart-line mr-2 text-purple-500"></i>
                                View All Progress
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Project Progress Overview -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Project Progress</h4>
                                    <template x-for="project in projectProgress" :key="project.project_id">
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h5 class="font-medium text-gray-900" x-text="project.project_name"></h5>
                                                <span class="text-sm text-gray-500" x-text="project.completion_percentage + '%'"></span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-indigo-600 h-2 rounded-full" :style="'width: ' + project.completion_percentage + '%'"></div>
                                            </div>
                                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                                <span x-text="project.completed_tasks + ' / ' + project.total_tasks + ' tasks'"></span>
                                                <span x-text="project.status"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Team Performance -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900">Team Performance</h4>
                                    <template x-for="member in teamPerformance" :key="member.user_id">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-900" x-text="member.full_name"></p>
                                                <p class="text-sm text-gray-500" x-text="member.role"></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900" x-text="member.completed_tasks + ' / ' + member.total_tasks"></p>
                                                <p class="text-xs text-gray-500">tasks completed</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </main>
        </div>
    </div>

    <script>
        function leaderDashboard() {
            return {
                // Data
                stats: {
                    projects: 0,
                    total_tasks: 0,
                    pending_tasks: 0,
                    completed_tasks: 0,
                    completed_projects: 0,
                    completed_on_time: 0,
                    completed_late: 0
                },
                projects: [],
                teamMembers: [],
                tasks: [],
                recentTasks: [],
                projectProgress: [],
                teamPerformance: [],
                completedProjects: [],
                
                // Assignment
                assignment: {
                    project_id: '',
                    task_title: '',
                    assignee: ''
                },
                
                // Filters
                priorityFilter: 'all',
                statusFilter: 'all',
                
                // Computed
                get filteredTasks() {
                    if (this.priorityFilter === 'all') return this.tasks;
                    return this.tasks.filter(task => task.priority === this.priorityFilter);
                },
                
                get filteredTasksByStatus() {
                    if (this.statusFilter === 'all') return this.tasks;
                    return this.tasks.filter(task => task.status === this.statusFilter);
                },
                
                // Methods
                init() {
                    this.loadDashboardData();
                },
                
                async loadDashboardData() {
                    try {
                        // Load stats and data
                        const response = await fetch('/leader/dashboard-data');
                        const data = await response.json();
                        
                        this.stats = data.stats;
                        this.projects = data.projects;
                        this.tasks = data.tasks;
                        this.recentTasks = data.recent_tasks;
                        this.projectProgress = data.project_progress;
                        this.teamPerformance = data.team_performance;
                        this.completedProjects = data.completed_projects || [];
                    } catch (error) {
                        console.error('Failed to load dashboard data:', error);
                    }
                },
                
                async loadProjectTeam() {
                    if (!this.assignment.project_id) return;
                    
                    try {
                        const response = await fetch(`/leader/projects/${this.assignment.project_id}/team-members`);
                        const data = await response.json();
                        this.teamMembers = data.team_members;
                    } catch (error) {
                        console.error('Failed to load team members:', error);
                    }
                },
                
                async quickAssignTask() {
                    if (!this.assignment.project_id || !this.assignment.task_title || !this.assignment.assignee) {
                        alert('Please fill all fields');
                        return;
                    }
                    
                    try {
                        const response = await fetch('/leader/quick-assign-task', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.assignment)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert('Task assigned successfully!');
                            this.assignment = { project_id: '', task_title: '', assignee: '' };
                            this.loadDashboardData();
                        } else {
                            alert('Failed to assign task: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Failed to assign task:', error);
                        alert('Failed to assign task');
                    }
                },
                
                async updateTaskPriority(taskId, priority) {
                    try {
                        const response = await fetch(`/leader/tasks/${taskId}/update-priority`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ priority })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Update local data
                            const task = this.tasks.find(t => t.card_id === taskId);
                            if (task) task.priority = priority;
                        } else {
                            alert('Failed to update priority');
                        }
                    } catch (error) {
                        console.error('Failed to update priority:', error);
                    }
                },
                
                async updateTaskStatus(taskId, status) {
                    try {
                        const response = await fetch(`/leader/tasks/${taskId}/update-status`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ status })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Update local data
                            const task = this.tasks.find(t => t.card_id === taskId);
                            if (task) task.status = status;
                            this.loadDashboardData(); // Refresh stats
                        } else {
                            alert('Failed to update status');
                        }
                    } catch (error) {
                        console.error('Failed to update status:', error);
                    }
                }
            }
        }
    </script>
</body>
</html>