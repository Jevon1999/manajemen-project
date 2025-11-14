@extends('layout.app')

@section('title', 'My Tasks - ' . ucfirst($userRole))
@section('page-title', 'My Tasks')
@section('page-description', 'Manage your assigned tasks and track your work progress')

@section('content')
<div id="taskApp" class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5" data-aos="fade-up">
        <!-- Total Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-blue-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-yellow-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                            <dd class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Todo -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-gray-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Todo</dt>
                            <dd class="text-2xl font-bold text-gray-600">{{ $stats['todo'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-purple-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Review</dt>
                            <dd class="text-2xl font-bold text-purple-600">{{ $stats['review'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                            <dd class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Tracking Summary -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white" data-aos="fade-up" data-aos-delay="100">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold mb-1">Total Time Logged</h3>
                <p class="text-3xl font-bold">{{ number_format($totalTimeLogged / 60, 1) }} hours</p>
                <p class="text-sm opacity-90 mt-1">{{ $totalTimeLogged }} minutes tracked</p>
            </div>
            <div class="text-right">
                <svg class="h-20 w-20 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Task Limit Warning -->
    @if($hasActiveTask)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg" data-aos="fade-up" data-aos-delay="200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700 font-medium">
                    <span class="font-semibold">Active Task Limit:</span> You already have an active task. Please complete it before starting a new one.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter and Search -->
    <div class="bg-white shadow rounded-lg p-4" data-aos="fade-up" data-aos-delay="300">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input v-model="filters.search" type="text" placeholder="Search tasks..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select v-model="filters.status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="todo">Todo</option>
                    <option value="in_progress">In Progress</option>
                    <option value="review">Review</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select v-model="filters.priority" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Priority</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                <select v-model="filters.sortBy" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="due_date">Due Date</option>
                    <option value="priority">Priority</option>
                    <option value="created_at">Created Date</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="grid grid-cols-1 gap-4" data-aos="fade-up" data-aos-delay="400">
        <div v-for="task in filteredTasks" :key="task.card_id" 
             class="bg-white shadow rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Task Header -->
                        <div class="flex items-center space-x-3 mb-3">
                            <span :class="getPriorityClass(task.priority)" 
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                @{{ getPriorityLabel(task.priority) }}
                            </span>
                            <span :class="getStatusClass(task.status)" 
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                @{{ getStatusLabel(task.status) }}
                            </span>
                            @if($userRole === 'designer')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                                Design Task
                            </span>
                            @endif
                        </div>

                        <!-- Task Title -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">@{{ task.card_title }}</h3>
                        
                        <!-- Task Description -->
                        <p v-if="task.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                            @{{ task.description }}
                        </p>

                        <!-- Task Meta Info -->
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span v-if="task.due_date">Due: @{{ formatDate(task.due_date) }}</span>
                                <span v-else class="text-gray-400">No deadline</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>@{{ calculateTimeSpent(task) }} logged</span>
                            </div>
                        </div>

                        <!-- Project Info -->
                        <div class="mt-3 flex items-center text-xs text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            @{{ task.board?.project?.project_name || 'No Project' }} › @{{ task.board?.board_name || 'No Board' }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="ml-4 flex flex-col space-y-2">
                        <button @click="viewTaskDetail(task.card_id)" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium shadow-sm">
                            View Details
                        </button>
                        
                        <button v-if="task.status !== 'in_progress' && !task.active_time_log" 
                                @click="quickStart(task.card_id)"
                                :disabled="hasActiveTask && task.status !== 'in_progress'"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                            Start
                        </button>

                        <button v-if="task.active_time_log" 
                                @click="quickStop(task.card_id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium shadow-sm animate-pulse">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
                            </svg>
                            Stop Timer
                        </button>
                    </div>
                </div>

                <!-- Running Timer Indicator -->
                <div v-if="task.active_time_log" class="mt-4 bg-red-50 border-l-4 border-red-400 p-3 rounded-r-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-400 animate-pulse mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-red-800">Timer Running</span>
                        </div>
                        <span class="text-sm font-bold text-red-600">
                            Started @{{ formatTime(task.active_time_log.start_time) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredTasks.length === 0" class="bg-white shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No tasks found</h3>
            <p class="mt-2 text-sm text-gray-500">Try adjusting your filters or check back later for new assignments.</p>
        </div>
    </div>

    <!-- Task Detail Modal -->
    <transition name="modal">
        <div v-if="showTaskModal" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeTaskModal"></div>
            
            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-5xl bg-white rounded-2xl shadow-2xl transform transition-all" @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="sticky top-0 z-10 bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xl font-bold text-white truncate">@{{ selectedTask?.card_title }}</h3>
                                    <p class="text-sm text-indigo-100">Task Details & Management</p>
                                </div>
                            </div>
                            <button @click="closeTaskModal" type="button" 
                                    class="ml-4 text-white hover:text-indigo-100 transition-colors p-2 rounded-full hover:bg-white hover:bg-opacity-10"
                                    title="Close (ESC)">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Running Timer Alert -->
                        <div v-if="isTimerRunning" class="mt-3 bg-white bg-opacity-10 backdrop-blur rounded-lg p-3 border border-white border-opacity-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-white animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-white font-semibold">Timer Running</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-white font-mono text-lg">@{{ currentTimerDuration }}</span>
                                    <button @click="stopTaskTimer" 
                                            class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                                        Stop Timer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 bg-gray-50 px-6">
                        <nav class="flex space-x-4" aria-label="Tabs">
                            <button @click="modalTab = 'overview'" 
                                    :class="modalTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                📊 Overview
                            </button>
                            <button @click="modalTab = 'tracking'" 
                                    :class="modalTab === 'tracking' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                ⏱️ Time Tracking
                            </button>
                            <button @click="modalTab = 'comments'" 
                                    :class="modalTab === 'comments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                💬 Comments <span v-if="taskDetail?.task_comments?.length" class="ml-1 bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full text-xs">@{{ taskDetail.task_comments.length }}</span>
                            </button>
                            <button @click="modalTab = 'files'" 
                                    :class="modalTab === 'files' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                📁 Files <span v-if="taskDetail?.attachments?.length" class="ml-1 bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full text-xs">@{{ taskDetail.attachments.length }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6 max-h-[calc(100vh-300px)] overflow-y-auto modal-content">
                        
                        <!-- Overview Tab -->
                        <div v-if="modalTab === 'overview'" class="space-y-6">
                            <!-- Status & Priority -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Current Status</label>
                                    <select v-model="updateForm.status" 
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="todo">📋 Todo</option>
                                        <option value="in_progress">⚡ In Progress</option>
                                        <option value="review">👀 Review</option>
                                        <option value="done">✅ Done</option>
                                    </select>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                                    <div class="flex items-center h-10">
                                        <span :class="getPriorityClass(taskDetail?.priority)" 
                                              class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium">
                                            @{{ getPriorityLabel(taskDetail?.priority) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
                                <p v-if="taskDetail?.description" class="text-gray-700 whitespace-pre-wrap">@{{ taskDetail.description }}</p>
                                <p v-else class="text-gray-400 italic">No description provided</p>
                            </div>

                            <!-- Progress Note -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Progress Update Note
                                    <span class="text-xs text-gray-500 font-normal ml-2">(Required for status changes)</span>
                                </label>
                                <textarea v-model="updateForm.description" 
                                          rows="3"
                                          placeholder="Describe what you've accomplished, challenges faced, or next steps..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">@{{ updateForm.description.length }}/1000 characters</span>
                                    <button @click="updateProgress" 
                                            :disabled="loading || !updateForm.description"
                                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                                        <span v-if="!loading">Update Progress</span>
                                        <span v-else>Updating...</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Task Meta Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-500">Due Date</p>
                                            <p class="text-sm font-semibold text-gray-900">@{{ formatDate(taskDetail?.due_date) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-500">Estimated Hours</p>
                                            <p class="text-sm font-semibold text-gray-900">@{{ taskDetail?.estimated_hours || 'N/A' }} hours</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-500">Created By</p>
                                            <p class="text-sm font-semibold text-gray-900">@{{ taskDetail?.creator?.full_name }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-500">Project</p>
                                            <p class="text-sm font-semibold text-gray-900">@{{ taskDetail?.board?.project?.project_name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking Tab -->
                        <div v-if="modalTab === 'tracking'" class="space-y-6">
                            <!-- Timer Controls -->
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">Time Tracker</h4>
                                        <p class="text-sm text-gray-600 mt-1">Track your work time on this task</p>
                                    </div>
                                    <div v-if="!isTimerRunning">
                                        <button @click="startTaskTimer" 
                                                :disabled="loading"
                                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md disabled:opacity-50">
                                            <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                            </svg>
                                            Start Timer
                                        </button>
                                    </div>
                                </div>

                                <!-- Current Timer Display -->
                                <div v-if="isTimerRunning" class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="relative">
                                                <svg class="animate-spin h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Current Session</p>
                                                <p class="text-3xl font-bold text-indigo-600 font-mono">@{{ currentTimerDuration }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Started @{{ formatTime(taskDetail?.active_time_log?.start_time) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Summary -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Total Logged</p>
                                    <p class="text-2xl font-bold text-gray-900">@{{ taskDetail?.timeSpent || '0h' }}</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Estimated</p>
                                    <p class="text-2xl font-bold text-gray-900">@{{ taskDetail?.estimated_hours || 'N/A' }}h</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Sessions</p>
                                    <p class="text-2xl font-bold text-gray-900">@{{ taskDetail?.time_logs?.length || 0 }}</p>
                                </div>
                            </div>

                            <!-- Time Log History -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Time Log History</h4>
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    <div v-for="log in taskDetail?.time_logs" :key="log.log_id" 
                                         class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">@{{ log.duration_minutes || 'In Progress' }} minutes</p>
                                                    <p class="text-xs text-gray-500">@{{ formatDateTime(log.start_time) }}</p>
                                                </div>
                                            </div>
                                            <span v-if="log.end_time" class="text-sm font-semibold text-green-600">Completed</span>
                                            <span v-else class="text-sm font-semibold text-yellow-600 animate-pulse">Running...</span>
                                        </div>
                                    </div>
                                    <div v-if="!taskDetail?.time_logs?.length" class="text-center py-8 text-gray-400">
                                        <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm">No time logs yet. Start tracking your work!</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comments Tab -->
                        <div v-if="modalTab === 'comments'" class="space-y-6">
                            <!-- Add Comment Form -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Add Comment</label>
                                <textarea v-model="commentForm.comment" 
                                          rows="3"
                                          placeholder="Share your thoughts, updates, or ask questions..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mb-2"></textarea>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">@{{ commentForm.comment.length }}/2000 characters</span>
                                    <button @click="addComment" 
                                            :disabled="loading || !commentForm.comment.trim()"
                                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                                        Post Comment
                                    </button>
                                </div>
                            </div>

                            <!-- Comments List -->
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                <div v-for="comment in taskDetail?.task_comments" :key="comment.comment_id"
                                     class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                                @{{ getInitials(comment.user?.full_name) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-sm font-semibold text-gray-900">@{{ comment.user?.full_name }}</p>
                                                <p class="text-xs text-gray-500">@{{ formatDateTime(comment.created_at) }}</p>
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">@{{ comment.comment }}</p>
                                            
                                            <!-- System Comment Badge -->
                                            <span v-if="comment.type === 'system'" 
                                                  class="inline-flex items-center mt-2 px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                System Update
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="!taskDetail?.task_comments?.length" class="text-center py-12 text-gray-400">
                                    <svg class="mx-auto h-16 w-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <p class="text-sm font-medium">No comments yet</p>
                                    <p class="text-xs mt-1">Be the first to comment on this task</p>
                                </div>
                            </div>
                        </div>

                        <!-- Files Tab -->
                        <div v-if="modalTab === 'files'" class="space-y-6">
                            <!-- Upload Form -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File</label>
                                <div class="flex items-end space-x-3">
                                    <div class="flex-1">
                                        <input type="file" 
                                               ref="fileInput"
                                               @change="handleFileSelect"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        <p class="text-xs text-gray-500 mt-1">Max file size: 10MB</p>
                                    </div>
                                    <button @click="uploadFile" 
                                            :disabled="loading || !selectedFile"
                                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium whitespace-nowrap">
                                        Upload
                                    </button>
                                </div>
                            </div>

                            <!-- Files Grid -->
                            <div class="grid grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                                <div v-for="attachment in taskDetail?.attachments" :key="attachment.attachment_id"
                                     class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all group">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-2">
                                            <svg v-if="attachment.file_type === 'image'" class="h-8 w-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                            </svg>
                                            <svg v-else class="h-8 w-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <button @click="deleteFile(attachment.attachment_id)"
                                                class="opacity-0 group-hover:opacity-100 text-red-600 hover:text-red-700 transition-opacity">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <h5 class="text-sm font-semibold text-gray-900 truncate mb-1">@{{ attachment.original_filename }}</h5>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>@{{ attachment.file_size_formatted }}</span>
                                        <span>@{{ formatDate(attachment.created_at) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2">By @{{ attachment.user?.full_name }}</p>
                                    
                                    <a :href="attachment.file_url" target="_blank"
                                       class="mt-3 block w-full text-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded text-xs font-medium hover:bg-indigo-100 transition-colors">
                                        Download
                                    </a>
                                </div>
                                
                                <div v-if="!taskDetail?.attachments?.length" class="col-span-2 text-center py-12 text-gray-400">
                                    <svg class="mx-auto h-16 w-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm font-medium">No files uploaded</p>
                                    <p class="text-xs mt-1">Upload files to share with your team</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</div>

@push('scripts')
<style>
/* Modal Animation */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}
.modal-enter-from .relative {
    transform: scale(0.9) translateY(-20px);
}
.modal-leave-to .relative {
    transform: scale(0.9) translateY(20px);
}

/* Custom Scrollbar */
.modal-content::-webkit-scrollbar {
    width: 8px;
}
.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
.modal-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

@push('scripts')
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            tasks: @json($myTasks),
            hasActiveTask: @json($hasActiveTask),
            filters: {
                search: '',
                status: '',
                priority: '',
                sortBy: 'due_date'
            },
            loading: false,
            // Modal Data
            showTaskModal: false,
            selectedTask: null,
            taskDetail: null,
            modalTab: 'overview',
            // Forms
            updateForm: {
                status: '',
                description: ''
            },
            commentForm: {
                comment: ''
            },
            selectedFile: null,
            // Timer
            timerInterval: null,
            timerStartTime: null,
            currentTimerDuration: '00:00:00'
        }
    },
    computed: {
        filteredTasks() {
            let filtered = [...this.tasks];

            // Search filter
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(task => 
                    task.card_title.toLowerCase().includes(search) ||
                    (task.description && task.description.toLowerCase().includes(search))
                );
            }

            // Status filter
            if (this.filters.status) {
                filtered = filtered.filter(task => task.status === this.filters.status);
            }

            // Priority filter
            if (this.filters.priority) {
                filtered = filtered.filter(task => task.priority === this.filters.priority);
            }

            // Sort
            filtered.sort((a, b) => {
                if (this.filters.sortBy === 'priority') {
                    const priorityOrder = { high: 3, medium: 2, low: 1 };
                    return priorityOrder[b.priority] - priorityOrder[a.priority];
                } else if (this.filters.sortBy === 'due_date') {
                    return new Date(a.due_date || '9999-12-31') - new Date(b.due_date || '9999-12-31');
                } else if (this.filters.sortBy === 'created_at') {
                    return new Date(b.created_at) - new Date(a.created_at);
                }
                return 0;
            });

            return filtered;
        },
        isTimerRunning() {
            return this.taskDetail?.active_time_log && !this.taskDetail.active_time_log.end_time;
        }
    },
    methods: {
        getPriorityClass(priority) {
            const classes = {
                'high': 'bg-red-100 text-red-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'low': 'bg-green-100 text-green-800'
            };
            return classes[priority] || 'bg-gray-100 text-gray-800';
        },
        getPriorityLabel(priority) {
            const labels = {
                'high': '🔴 High',
                'medium': '🟡 Medium',
                'low': '🟢 Low'
            };
            return labels[priority] || priority;
        },
        getStatusClass(status) {
            const classes = {
                'todo': 'bg-gray-100 text-gray-800',
                'in_progress': 'bg-yellow-100 text-yellow-800',
                'review': 'bg-purple-100 text-purple-800',
                'done': 'bg-green-100 text-green-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        getStatusLabel(status) {
            const labels = {
                'todo': '📋 Todo',
                'in_progress': '⚡ In Progress',
                'review': '👀 Review',
                'done': '✅ Done'
            };
            return labels[status] || status;
        },
        formatDate(date) {
            if (!date) return 'No deadline';
            const d = new Date(date);
            const now = new Date();
            const diffDays = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
            
            const formatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            
            if (diffDays < 0) return `${formatted} (Overdue)`;
            if (diffDays === 0) return `${formatted} (Today)`;
            if (diffDays === 1) return `${formatted} (Tomorrow)`;
            if (diffDays <= 7) return `${formatted} (${diffDays} days)`;
            
            return formatted;
        },
        formatTime(datetime) {
            return new Date(datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        calculateTimeSpent(task) {
            if (!task.time_logs || task.time_logs.length === 0) return '0h';
            const totalMinutes = task.time_logs.reduce((sum, log) => sum + (log.duration_minutes || 0), 0);
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
        },
        viewTaskDetail(taskId) {
            this.loading = true;
            this.selectedTask = this.tasks.find(t => t.card_id === taskId);
            
            fetch(`/developer/tasks/${taskId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.taskDetail = data.task;
                    this.updateForm.status = data.task.status;
                    this.updateForm.description = '';
                    this.showTaskModal = true;
                    document.body.style.overflow = 'hidden';
                    
                    // Start timer display if running
                    if (data.isTimerRunning) {
                        this.startTimerDisplay();
                    }
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to load task details', 'error');
                this.loading = false;
            });
        },
        closeTaskModal() {
            this.showTaskModal = false;
            document.body.style.overflow = 'auto';
            this.stopTimerDisplay();
            this.modalTab = 'overview';
            this.selectedTask = null;
            this.taskDetail = null;
            this.selectedFile = null;
        },
        updateProgress() {
            if (this.loading || !this.updateForm.description.trim()) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/update-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.updateForm)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    this.updateForm.description = '';
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to update progress', 'error');
                this.loading = false;
            });
        },
        addComment() {
            if (this.loading || !this.commentForm.comment.trim()) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.commentForm)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('Comment added successfully!', 'success');
                    this.commentForm.comment = '';
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to add comment', 'error');
                this.loading = false;
            });
        },
        handleFileSelect(event) {
            this.selectedFile = event.target.files[0];
        },
        uploadFile() {
            if (this.loading || !this.selectedFile) return;
            
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/upload`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('File uploaded successfully!', 'success');
                    this.selectedFile = null;
                    this.$refs.fileInput.value = '';
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to upload file', 'error');
                this.loading = false;
            });
        },
        deleteFile(attachmentId) {
            if (!confirm('Are you sure you want to delete this file?')) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/attachments/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('File deleted successfully!', 'success');
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to delete file', 'error');
                this.loading = false;
            });
        },
        startTaskTimer() {
            if (this.loading) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/start-timer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to start timer', 'error');
                this.loading = false;
            });
        },
        stopTaskTimer() {
            if (this.loading) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${this.taskDetail.card_id}/stop-timer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    // Refresh task detail
                    this.viewTaskDetail(this.taskDetail.card_id);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to stop timer', 'error');
                this.loading = false;
            });
        },
        startTimerDisplay() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            this.timerStartTime = new Date(this.taskDetail.active_time_log.start_time);
            this.timerInterval = setInterval(() => {
                const now = new Date();
                const diff = now - this.timerStartTime;
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                this.currentTimerDuration = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        },
        stopTimerDisplay() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },
        formatDateTime(datetime) {
            if (!datetime) return 'N/A';
            const date = new Date(datetime);
            return date.toLocaleString('id-ID', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        },
        quickStart(taskId) {
            if (this.loading) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${taskId}/start-timer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to start timer', 'error');
                this.loading = false;
            });
        },
        quickStop(taskId) {
            if (this.loading) return;
            
            this.loading = true;
            fetch(`/developer/tasks/${taskId}/stop-timer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(data.message, 'error');
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Failed to stop timer', 'error');
                this.loading = false;
            });
        },
        showNotification(message, type = 'success') {
            // Reuse notification system from project management
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    },
    mounted() {
        // ESC key handler for closing modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.showTaskModal) {
                this.closeTaskModal();
            }
        });
    },
    beforeUnmount() {
        // Cleanup timer interval
        this.stopTimerDisplay();
    }
}).mount('#taskApp');
</script>
@endpush
@endsection
