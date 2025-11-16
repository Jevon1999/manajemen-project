@extends('layout.app')

@section('title', 'Project Dashboard - ' . $project->project_name)

@section('page-title', $project->project_name)
@section('page-description', 'Manage tasks and monitor team progress')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $project->project_name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $project->description ?? 'No description available' }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            @if($project->status !== 'completed')
            <button onclick="markProjectComplete()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tandai Selesai
            </button>
            @else
            <button onclick="reopenProject()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Buka Kembali
            </button>
            @endif
            
            <a href="{{ route('leader.tasks.create', $project->project_id) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Task
            </a>
            <a href="{{ route('leader.projects') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Projects
            </a>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalTasks }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ $completedTasks }}
                                @if($totalTasks > 0)
                                    <span class="text-sm text-gray-500">({{ round(($completedTasks / $totalTasks) * 100) }}%)</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $inProgressTasks }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Priority Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">High Priority</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $criticalTasks + $highTasks }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Task Status Breakdown -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Task Status Overview</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- To Do -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-gray-400 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700">To Do</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900 mr-3">{{ $todoTasks }}</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-400 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($todoTasks / $totalTasks) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700">In Progress</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900 mr-3">{{ $inProgressTasks }}</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($inProgressTasks / $totalTasks) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Review -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700">Review</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900 mr-3">{{ $reviewTasks }}</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($reviewTasks / $totalTasks) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Done -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700">Done</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900 mr-3">{{ $completedTasks }}</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Tasks</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($recentTasks as $task)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('leader.tasks.show', [$project->project_id, $task->card_id]) }}" 
                                       class="hover:text-indigo-600">
                                        {{ $task->card_title }}
                                    </a>
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Board: {{ $task->board->board_name ?? 'Unknown' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->priority === 'critical') bg-red-100 text-red-800
                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800  
                                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->status === 'done') bg-green-100 text-green-800
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($task->status === 'review') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center text-xs text-gray-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Created {{ $task->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
                        <div class="mt-6">
                            <a href="{{ route('leader.tasks.create', $project->project_id) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Task
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Team Performance -->
        <div class="space-y-6">
            <!-- Priority Breakdown -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Priority Breakdown</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-red-500 mr-2">🚨</span>
                                <span class="text-sm text-gray-700">Critical</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $criticalTasks }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-orange-500 mr-2">🔴</span>
                                <span class="text-sm text-gray-700">High</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $highTasks }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-yellow-500 mr-2">🟡</span>
                                <span class="text-sm text-gray-700">Medium</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $mediumTasks }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">🟢</span>
                                <span class="text-sm text-gray-700">Low</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $lowTasks }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Performance -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Team Performance</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($teamPerformance as $member)
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr($member->user->full_name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $member->user->full_name }}</p>
                                <p class="text-xs text-gray-500">
                                    @if($member->role === 'developer')
                                        💻 Developer
                                    @elseif($member->role === 'designer')
                                        🎨 Designer
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            <div class="text-center">
                                <div class="font-medium text-gray-900">{{ $member->total_tasks }}</div>
                                <div class="text-gray-500">Total</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-green-600">{{ $member->completed_tasks }}</div>
                                <div class="text-gray-500">Done</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-blue-600">{{ $member->active_tasks }}</div>
                                <div class="text-gray-500">Active</div>
                            </div>
                        </div>
                        @if($member->overdue_tasks > 0)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $member->overdue_tasks }} overdue
                            </span>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500">No team members found</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Project Modal (Alpine.js) -->
<div x-data="{ 
    showCompleteModal: false,
    completionNotes: '',
    delayReason: '',
    isOverdue: {{ $project->isOverdue() ? 'true' : 'false' }},
    delayDays: {{ $project->isOverdue() ? now()->diffInDays($project->deadline) : 0 }},
    submitCompletion() {
        const form = document.getElementById('complete-form');
        form.submit();
    }
}" x-cloak>
    <!-- Modal Backdrop -->
    <div x-show="showCompleteModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4"
         @click.self="showCompleteModal = false">
        
        <!-- Modal Content -->
        <div x-show="showCompleteModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-lg shadow-xl max-w-md w-full p-6"
             @click.stop>
            
            <!-- Overdue Warning (if applicable) -->
            <div x-show="isOverdue" class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            ⚠️ Project Terlambat
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Deadline: {{ $project->deadline ? $project->deadline->format('d M Y') : '-' }}</p>
                            <p>Hari ini: {{ now()->format('d M Y') }}</p>
                            <p class="font-semibold mt-1">Terlambat: <span x-text="delayDays"></span> hari</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span x-show="!isOverdue">✅ Selesaikan Project</span>
                    <span x-show="isOverdue">⚠️ Selesaikan Project (Terlambat)</span>
                </h3>
                <button @click="showCompleteModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="complete-form" method="POST" action="{{ route('leader.projects.complete', $project->project_id) }}">
                @csrf
                
                <!-- Completion Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Penyelesaian
                    </label>
                    <textarea name="completion_notes" 
                              x-model="completionNotes"
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Tambahkan catatan mengenai penyelesaian project..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Optional - dokumentasi hasil project</p>
                </div>

                <!-- Delay Reason (if overdue) -->
                <div x-show="isOverdue" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Keterlambatan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="delay_reason" 
                              x-model="delayReason"
                              rows="3" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                              placeholder="Jelaskan alasan project terlambat diselesaikan..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Wajib diisi untuk project yang terlambat</p>
                </div>

                <input type="hidden" name="is_overdue" :value="isOverdue ? '1' : '0'">

                <!-- Actions -->
                <div class="flex space-x-3 mt-6">
                    <button type="button" 
                            @click="showCompleteModal = false"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 text-white rounded-lg transition-colors"
                            :class="isOverdue ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'">
                        <span x-show="!isOverdue">✅ Selesaikan</span>
                        <span x-show="isOverdue">⚠️ Selesaikan Dengan Catatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trigger: Update markProjectComplete to show modal -->
    <script>
        // Override the global function to use Alpine modal
        window.markProjectComplete = function() {
            const alpineComponent = document.querySelector('[x-data]').__x.$data;
            alpineComponent.showCompleteModal = true;
        };
    </script>
</div>

<script>
// markProjectComplete is already defined in Alpine component above

function reopenProject() {
    if (!confirm('Apakah Anda yakin ingin membuka kembali project yang sudah selesai ini?')) {
        return;
    }
    
    fetch(`/projects/{{ $project->project_id }}/reopen`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Gagal membuka kembali project');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat membuka kembali project');
    });
}
</script>
@endsection