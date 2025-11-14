@extends('layout.app')

@section('title', 'Detail Project')

@section('page-title', $project->project_name)
@section('page-description', 'Kelola project dan anggota tim')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Project Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $project->project_name }}</h1>
                            <p class="text-gray-600">{{ $project->description ?: 'Tidak ada deskripsi' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"/>
                            </svg>
                            <span>Mulai: {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</span>
                        </div>
                        @if($project->end_date)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"/>
                            </svg>
                            <span>Selesai: {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</span>
                        </div>
                        @endif
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Dibuat oleh: {{ $project->creator->full_name }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                @if($project->status === 'active') bg-green-100 text-green-800
                                @elseif($project->status === 'planning') bg-yellow-100 text-yellow-800
                                @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('projects.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                        Kembali
                    </a>
                    
                    @if(Auth::user()->user_id === $project->leader_id && Auth::user()->role === 'leader')
                    <button onclick="openAddMemberModal()" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Anggota
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Task Statistics & Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Task Statistics -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">📋 Task Overview</h2>
                <a href="{{ route('admin.projects.tasks.index', $project->project_id) }}" 
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View All →
                </a>
            </div>
            
            @if($taskStatistics && $taskStatistics['total'] > 0)
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $taskStatistics['total'] }}</div>
                    <div class="text-xs text-gray-600">Total Tasks</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">{{ $taskStatistics['completion_rate'] }}%</div>
                    <div class="text-xs text-green-600">Completion Rate</div>
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">📋 Todo</span>
                    <span class="font-medium text-gray-900">{{ $taskStatistics['todo'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-600">🚀 In Progress</span>
                    <span class="font-medium text-blue-700">{{ $taskStatistics['in_progress'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-yellow-600">👀 Review</span>
                    <span class="font-medium text-yellow-700">{{ $taskStatistics['review'] }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-green-600">✅ Done</span>
                    <span class="font-medium text-green-700">{{ $taskStatistics['done'] }}</span>
                </div>
                @if($taskStatistics['overdue'] > 0)
                <div class="flex items-center justify-between text-sm pt-2 border-t border-gray-200">
                    <span class="text-red-600">⏰ Overdue</span>
                    <span class="font-medium text-red-700">{{ $taskStatistics['overdue'] }}</span>
                </div>
                @endif
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                    <span>Progress</span>
                    <span>{{ $taskStatistics['done'] }}/{{ $taskStatistics['total'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full transition-all" 
                         style="width: {{ $taskStatistics['completion_rate'] }}%"></div>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 text-sm">No tasks yet</p>
                @if(Auth::user()->user_id === $project->leader_id && Auth::user()->role === 'leader')
                <a href="{{ route('admin.projects.tasks.create', $project->project_id) }}" 
                   class="inline-block mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Create First Task →
                </a>
                @endif
            </div>
            @endif
        </div>
        
        <!-- Requests Attention -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @php
                $isLeader = Auth::user()->user_id === $project->leader_id && Auth::user()->role === 'leader';
                
                // Get pending extension requests for this project's tasks
                $pendingRequests = \App\Models\ExtensionRequest::where('status', 'pending')
                    ->where('entity_type', 'task')
                    ->whereHas('task', function($q) use ($project) {
                        $q->where('project_id', $project->project_id);
                    })
                    ->with(['task', 'requester'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            @endphp

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">🔔 Requests Attention</h2>
                @if($pendingRequests->count() > 0)
                    <span class="px-3 py-1 text-xs font-bold bg-gradient-to-r from-orange-400 to-red-400 text-white rounded-full animate-pulse">
                        {{ $pendingRequests->count() }}
                    </span>
                @endif
            </div>
            
            @if($isLeader && $pendingRequests->count() > 0)
                <div class="space-y-3 mb-4">
                    @foreach($pendingRequests->take(3) as $request)
                        <div class="border-l-4 border-orange-400 bg-orange-50 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $request->task->title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <img src="{{ $request->requester->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($request->requester->name) }}" 
                                             alt="{{ $request->requester->name }}"
                                             class="w-5 h-5 rounded-full">
                                        <span class="text-xs text-gray-600">{{ $request->requester->name }}</span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-500">{{ $request->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                                <div>
                                    <span class="text-gray-600">Old:</span>
                                    <span class="font-medium text-red-600">{{ $request->old_deadline->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">New:</span>
                                    <span class="font-medium text-green-600">{{ $request->requested_deadline->format('d M Y') }}</span>
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-700 mb-3 line-clamp-2">{{ $request->reason }}</p>
                            
                            <div class="flex gap-2">
                                <button onclick="approveQuickRequest({{ $request->id }})" 
                                        class="flex-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition">
                                    ✓ Approve
                                </button>
                                <button onclick="rejectQuickRequest({{ $request->id }}, '{{ addslashes($request->task->title) }}')" 
                                        class="flex-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition">
                                    ✗ Reject
                                </button>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($pendingRequests->count() > 3)
                        <a href="{{ route('extension-requests.index') }}" 
                           class="block text-center py-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                            View All {{ $pendingRequests->count() }} Requests →
                        </a>
                    @endif
                </div>
            @elseif($isLeader)
                <div class="text-center py-6">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">No pending extension requests</p>
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm text-gray-500 font-medium">Request Notifications</p>
                    <p class="text-xs text-gray-400 mt-1">Extension requests will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Project Members -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Anggota Tim</h2>
                    <span class="text-sm text-gray-500">{{ $project->members->count() }} anggota</span>
                </div>
                
                <div id="members-list" class="space-y-4">
                    @forelse($project->members as $member)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg" data-member-id="{{ $member->member_id }}">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">
                                    {{ substr($member->user->full_name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $member->user->full_name }}</p>
                                <p class="text-sm text-gray-600">{{ $member->user->email }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1
                                    @if($member->role === 'project_manager') bg-purple-100 text-purple-800
                                    @elseif($member->role === 'developer') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    @if($member->role === 'project_manager') 👨‍💼 Project Manager
                                    @elseif($member->role === 'developer') 👨‍💻 Developer
                                    @else 🎨 Designer @endif
                                </span>
                            </div>
                        </div>
                        
                        @if(Auth::user()->user_id === $project->leader_id && Auth::user()->role === 'leader')
                        <div class="flex space-x-2">
                            @if($member->role !== 'project_manager' || Auth::user()->role === 'admin')
                            <button onclick="removeMember({{ $member->member_id }})" 
                                    class="text-red-600 hover:text-red-800 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        <p class="text-gray-500">Belum ada anggota dalam project ini</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Project Statistics -->
        <div class="space-y-6">
            <!-- Project Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Project</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Anggota:</span>
                        <span class="font-medium">{{ $project->members->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Project Manager:</span>
                        <span class="font-medium">
                            @php $pm = $project->members->where('role', 'project_manager')->first(); @endphp
                            {{ $pm ? $pm->user->full_name : 'Belum assigned' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Developer:</span>
                        <span class="font-medium">{{ $project->members->where('role', 'developer')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Designer:</span>
                        <span class="font-medium">{{ $project->members->where('role', 'designer')->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Work Time History -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">History Time Log</h3>
                    <button onclick="toggleFilterForm()" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                </div>

                <!-- Filter Form (Hidden by default) -->
                <div id="filterForm" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <form method="GET" action="{{ route('admin.projects.show', $project->project_id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Team Member</label>
                            <select name="user_id" class="w-full px-3 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Members</option>
                                @foreach($project->members as $member)
                                    <option value="{{ $member->user_id }}" {{ request('user_id') == $member->user_id ? 'selected' : '' }}>
                                        {{ $member->user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                Apply
                            </button>
                            <a href="{{ route('admin.projects.show', $project->project_id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition-colors">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Time Summary - Enhanced -->
                <div class="grid grid-cols-3 gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <div class="text-xs text-blue-600 font-medium mb-1">Today</div>
                        <div class="text-xl font-bold text-blue-700">
                            @php
                                $todayHours = floor(($todayTotal ?? 0) / 3600);
                                $todayMinutes = floor((($todayTotal ?? 0) % 3600) / 60);
                            @endphp
                            {{ sprintf('%02d:%02d', $todayHours, $todayMinutes) }}
                        </div>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-xs text-green-600 font-medium mb-1">This Week</div>
                        <div class="text-xl font-bold text-green-700">
                            @php
                                $weekHours = floor(($weekTotal ?? 0) / 3600);
                                $weekMinutes = floor((($weekTotal ?? 0) % 3600) / 60);
                            @endphp
                            {{ sprintf('%02d:%02d', $weekHours, $weekMinutes) }}
                        </div>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <div class="text-xs text-purple-600 font-medium mb-1">This Month</div>
                        <div class="text-xl font-bold text-purple-700">
                            @php
                                $monthHours = floor(($monthTotal ?? 0) / 3600);
                                $monthMinutes = floor((($monthTotal ?? 0) % 3600) / 60);
                            @endphp
                            {{ sprintf('%02d:%02d', $monthHours, $monthMinutes) }}
                        </div>
                    </div>
                </div>

                <!-- Top Contributor Badge -->
                @if($topContributor)
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-medium text-yellow-800">Top Contributor This Week</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-semibold text-yellow-900">{{ $topContributor->user->full_name ?? 'N/A' }}</span>
                        <span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs font-bold rounded">
                            {{ sprintf('%02d:%02d', floor($topContributor->total / 3600), floor(($topContributor->total % 3600) / 60)) }}
                        </span>
                    </div>
                </div>
                @endif

                <!-- Time Log Entries -->
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($workSessions ?? [] as $session)
                    <div class="flex items-center justify-between py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-medium text-blue-600">
                                        {{ substr($session->user->full_name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">
                                        {{ $session->user->full_name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $session->task->title ?? 'General Work' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 flex-shrink-0 ml-4">
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $session->formatted_duration }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $session->work_date->format('M j') }}
                                </div>
                            </div>
                            @if($session->work_date->isToday())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Today
                            </span>
                            @elseif($session->work_date->isYesterday())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Yesterday
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">No work sessions recorded yet</p>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($workSessions->hasPages())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing {{ $workSessions->firstItem() }} to {{ $workSessions->lastItem() }} of {{ $workSessions->total() }} sessions
                        </div>
                        <div class="flex space-x-1">
                            {{-- Previous Button --}}
                            @if($workSessions->onFirstPage())
                                <span class="px-3 py-1 text-sm bg-gray-100 text-gray-400 rounded cursor-not-allowed">Previous</span>
                            @else
                                <a href="{{ $workSessions->previousPageUrl() }}" class="px-3 py-1 text-sm bg-white text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach($workSessions->getUrlRange(1, $workSessions->lastPage()) as $page => $url)
                                @if($page == $workSessions->currentPage())
                                    <span class="px-3 py-1 text-sm bg-blue-600 text-white rounded">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-1 text-sm bg-white text-gray-700 border border-gray-300 rounded hover:bg-gray-50">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next Button --}}
                            @if($workSessions->hasMorePages())
                                <a href="{{ $workSessions->nextPageUrl() }}" class="px-3 py-1 text-sm bg-white text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Next</a>
                            @else
                                <span class="px-3 py-1 text-sm bg-gray-100 text-gray-400 rounded cursor-not-allowed">Next</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Anggota Baru</h3>
                    <button onclick="closeAddMemberModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form id="addMemberForm">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->project_id }}">
                    
                    <!-- User Search -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari User</label>
                        <div class="relative">
                            <input type="text" 
                                   id="user_search" 
                                   placeholder="Cari berdasarkan nama, username, atau email..."
                                   autocomplete="off"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            
                            <!-- Search Results -->
                            <div id="user_dropdown" class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                <div id="user_results"></div>
                            </div>
                        </div>
                        
                        <!-- Selected User -->
                        <div id="selected_user" class="hidden mt-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium" id="user_initial"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900" id="user_name"></p>
                                        <p class="text-sm text-gray-600" id="user_info"></p>
                                    </div>
                                </div>
                                <button type="button" id="remove_user" class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <input type="hidden" id="user_id" name="user_id">
                    </div>
                    
                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role dalam Project</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Role</option>
                            <option value="developer">👨‍💻 Developer</option>
                            <option value="designer">🎨 Designer</option>
                        </select>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeAddMemberModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                            Tambah Anggota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeUserSearch();
    initializeAddMemberForm();
});

function openAddMemberModal() {
    document.getElementById('addMemberModal').classList.remove('hidden');
    document.getElementById('user_search').focus();
}

function closeAddMemberModal() {
    document.getElementById('addMemberModal').classList.add('hidden');
    document.getElementById('addMemberForm').reset();
    document.getElementById('selected_user').classList.add('hidden');
    document.getElementById('user_dropdown').classList.add('hidden');
}

function initializeUserSearch() {
    const searchInput = document.getElementById('user_search');
    const dropdown = document.getElementById('user_dropdown');
    const results = document.getElementById('user_results');
    const selectedUserDiv = document.getElementById('selected_user');
    const userIdInput = document.getElementById('user_id');
    const removeUserBtn = document.getElementById('remove_user');
    
    let searchTimeout;

    // Handle search input
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            dropdown.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchUsers(query);
        }, 300);
    });

    // Search users via API
    function searchUsers(query) {
        fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            displayUserResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
            results.innerHTML = '<div class="p-3 text-red-600">Terjadi kesalahan saat mencari user</div>';
            dropdown.classList.remove('hidden');
        });
    }

    // Display search results
    function displayUserResults(users) {
        if (users.length === 0) {
            results.innerHTML = '<div class="p-3 text-gray-500">Tidak ada user ditemukan</div>';
        } else {
            results.innerHTML = users.map(user => `
                <div class="user-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-user-id="${user.user_id}" 
                     data-user-name="${user.full_name}"
                     data-user-username="${user.username}"
                     data-user-email="${user.email}">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">${user.full_name.charAt(0)}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">${user.full_name}</p>
                            <p class="text-sm text-gray-600">@${user.username} • ${user.email}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        dropdown.classList.remove('hidden');
    }

    // Handle user selection
    results.addEventListener('click', function(e) {
        const userItem = e.target.closest('.user-item');
        if (!userItem) return;
        
        const userId = userItem.dataset.userId;
        const userName = userItem.dataset.userName;
        const userUsername = userItem.dataset.userUsername;
        const userEmail = userItem.dataset.userEmail;
        
        selectUser(userId, userName, userUsername, userEmail);
    });

    // Select user
    function selectUser(id, name, username, email) {
        userIdInput.value = id;
        searchInput.value = '';
        dropdown.classList.add('hidden');
        
        // Show selected user
        document.getElementById('user_initial').textContent = name.charAt(0);
        document.getElementById('user_name').textContent = name;
        document.getElementById('user_info').textContent = `@${username} • ${email}`;
        selectedUserDiv.classList.remove('hidden');
    }

    // Remove selected user
    removeUserBtn.addEventListener('click', function() {
        userIdInput.value = '';
        selectedUserDiv.classList.add('hidden');
        searchInput.value = '';
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

function initializeAddMemberForm() {
    const form = document.getElementById('addMemberForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('{{ route("projects.members.store", $project->project_id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new member to the list
                addMemberToList(data.member);
                closeAddMemberModal();
                
                // Show success message
                showMessage('Anggota berhasil ditambahkan!', 'success');
            } else {
                showMessage(data.message || 'Gagal menambahkan anggota', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Terjadi kesalahan saat menambahkan anggota', 'error');
        });
    });
}

function addMemberToList(member) {
    const membersList = document.getElementById('members-list');
    
    const roleInfo = {
        'project_manager': { icon: '👨‍💼', label: 'Project Manager', class: 'bg-purple-100 text-purple-800' },
        'developer': { icon: '👨‍💻', label: 'Developer', class: 'bg-blue-100 text-blue-800' },
        'designer': { icon: '🎨', label: 'Designer', class: 'bg-green-100 text-green-800' }
    };
    
    const role = roleInfo[member.role] || roleInfo['developer'];
    
    const memberHtml = `
        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg" data-member-id="${member.member_id}">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">
                        ${member.user.full_name.charAt(0)}
                    </span>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${member.user.full_name}</p>
                    <p class="text-sm text-gray-600">${member.user.email}</p>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1 ${role.class}">
                        ${role.icon} ${role.label}
                    </span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="removeMember(${member.member_id})" 
                        class="text-red-600 hover:text-red-800 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    // Check if there's an empty state message
    const emptyState = membersList.querySelector('.text-center.py-8');
    if (emptyState) {
        membersList.innerHTML = memberHtml;
    } else {
        membersList.insertAdjacentHTML('beforeend', memberHtml);
    }
}

function removeMember(memberId) {
    if (!confirm('Apakah Anda yakin ingin menghapus anggota ini dari project?')) {
        return;
    }
    
    fetch(`/project-members/${memberId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove member from the list
            const memberElement = document.querySelector(`[data-member-id="${memberId}"]`);
            if (memberElement) {
                memberElement.remove();
            }
            
            showMessage('Anggota berhasil dihapus!', 'success');
        } else {
            showMessage(data.message || 'Gagal menghapus anggota', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Terjadi kesalahan saat menghapus anggota', 'error');
    });
}

function showMessage(message, type) {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Toggle filter form
function toggleFilterForm() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm.classList.contains('hidden')) {
        filterForm.classList.remove('hidden');
        filterForm.style.animation = 'slideDown 0.2s ease-out';
    } else {
        filterForm.classList.add('hidden');
    }
}

// Approve extension request from quick action
function approveQuickRequest(requestId) {
    if (!confirm('Approve this extension request?')) {
        return;
    }
    
    fetch(`/extension-requests/${requestId}/approve`, {
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
            showMessage('✅ Extension request approved!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || '❌ Failed to approve request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('❌ Error approving request', 'error');
    });
}

// Reject extension request from quick action
function rejectQuickRequest(requestId, taskTitle) {
    const reason = prompt(`Reject extension request for "${taskTitle}"?\n\nPlease enter rejection reason (10-500 characters):`);
    
    if (!reason) return;
    
    if (reason.length < 10) {
        alert('Rejection reason must be at least 10 characters');
        return;
    }
    
    if (reason.length > 500) {
        alert('Rejection reason must not exceed 500 characters');
        return;
    }
    
    fetch(`/extension-requests/${requestId}/reject`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('✅ Extension request rejected', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || '❌ Failed to reject request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('❌ Error rejecting request', 'error');
    });
}
</script>
@endsection