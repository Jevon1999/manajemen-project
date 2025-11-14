@extends('layout.layout')

@section('title', $task->title)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('admin.projects.tasks.index', $task->project_id) }}" class="hover:text-gray-700">{{ $task->project ? $task->project->project_name : 'Unknown Project' }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-900 font-medium">{{ Str::limit($task->title, 50) }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Task Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Task Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $task->title }}</h1>
                        <div class="flex items-center flex-wrap gap-2">
                            <!-- Status -->
                            @if($task->status === 'todo')
                                <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full">To Do</span>
                            @elseif($task->status === 'in_progress')
                                <span class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">In Progress</span>
                            @elseif($task->status === 'review')
                                <span class="px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">Review</span>
                            @elseif($task->status === 'done')
                                <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">✓ Done</span>
                            @endif

                            <!-- Priority -->
                            @if($task->priority === 'high')
                                <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">🔴 High Priority</span>
                            @elseif($task->priority === 'medium')
                                <span class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">🟡 Medium Priority</span>
                            @elseif($task->priority === 'low')
                                <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">🟢 Low Priority</span>
                            @endif

                            <!-- Blocked Badge -->
                            @if($task->is_blocked)
                                <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full border-2 border-red-300">
                                    🔒 Blocked
                                </span>
                            @endif

                            <!-- Overdue Badge -->
                            @if($task->due_date && $task->due_date < now() && $task->status !== 'done')
                                <span class="px-3 py-1 text-sm font-medium bg-orange-100 text-orange-800 rounded-full">
                                    ⏰ Overdue
                                </span>
                            @endif
                        </div>

                        <!-- Extension Request Button -->
                        @php
                            $isOverdue = $task->isOverdue();
                            $isAssigned = $task->assigned_to === Auth::id();
                            $hasPendingRequest = $task->hasPendingExtensionRequest();
                        @endphp

                        @if($isOverdue && $isAssigned)
                            <div class="mt-4">
                                @if($task->is_blocked && !$hasPendingRequest)
                                    <button 
                                        onclick="openExtensionModal()"
                                        class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Ajukan Perpanjangan Deadline
                                    </button>
                                    @if($task->block_reason)
                                        <p class="text-sm text-gray-600 mt-2">
                                            <span class="font-medium">Reason:</span> {{ $task->block_reason }}
                                        </p>
                                    @endif
                                @elseif($hasPendingRequest)
                                    <div class="flex items-center gap-2 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-yellow-800">Extension request pending approval from leader</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Leader: Extension Request Approval Section -->
                        @php
                            $isLeader = $task->project->leader_id === Auth::id();
                            $pendingRequest = $task->extensionRequests()
                                ->where('status', 'pending')
                                ->with('requester')
                                ->first();
                        @endphp
                        
                        @if($isLeader && $pendingRequest)
                        <div class="mt-4 p-4 bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-300 rounded-lg shadow-sm">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 mb-1">⏰ Extension Request Pending</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        <span class="font-semibold">{{ $pendingRequest->requester->full_name }}</span> meminta perpanjangan deadline
                                    </p>
                                    
                                    <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                        <div class="bg-white p-2 rounded border border-gray-200">
                                            <span class="text-gray-600">Current Deadline:</span>
                                            <p class="font-semibold text-red-700">{{ $pendingRequest->old_deadline->format('d M Y') }}</p>
                                        </div>
                                        <div class="bg-white p-2 rounded border border-gray-200">
                                            <span class="text-gray-600">Requested:</span>
                                            <p class="font-semibold text-green-700">{{ $pendingRequest->requested_deadline->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($pendingRequest->reason)
                                    <div class="bg-white p-3 rounded border border-gray-200 mb-3">
                                        <p class="text-xs text-gray-600 mb-1">Reason:</p>
                                        <p class="text-sm text-gray-800">{{ $pendingRequest->reason }}</p>
                                    </div>
                                    @endif

                                    <div class="flex gap-2">
                                        <button 
                                            onclick="approveExtensionRequest({{ $pendingRequest->id }})"
                                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve (+{{ $pendingRequest->getExtensionDays() }} hari)
                                        </button>
                                        
                                        <button 
                                            onclick="showRejectExtensionModal({{ $pendingRequest->id }})"
                                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Quick Status Actions for Tasks -->
                    @if(Auth::id() === $task->assigned_to && $task->status !== 'done' && !$task->is_blocked)
                    
                    <!-- Debug: Show any errors -->
                    @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong>Errors:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                    @endif
                    
                    <div class="flex space-x-2">
                        <!-- DEBUG: Show task status -->
                        <p style="background: yellow; padding: 10px; margin: 10px 0;">
                            DEBUG: Current Status = {{ $task->status }} | User = {{ Auth::id() }} | Assigned = {{ $task->assigned_to }}
                        </p>
                        
                        @if($task->status === 'in_progress')
                        <!-- Simple form without any fancy styling -->
                        <form method="POST" action="{{ route('admin.projects.tasks.updateStatus', [$project->project_id, $task->task_id]) }}" id="statusForm" style="border: 3px solid red; padding: 20px; background: lightyellow;">
                            @csrf
                            <input type="hidden" name="status" value="review">
                            <button type="submit" style="background: purple; color: white; padding: 15px 30px; font-size: 18px; border: none; cursor: pointer;">
                                KLIK DI SINI - Selesaikan Task (Review)
                            </button>
                        </form>
                        <script>
                            document.getElementById('statusForm').addEventListener('submit', function(e) {
                                console.log('=== FORM SUBMIT EVENT ===');
                                console.log('Action:', this.action);
                                console.log('Method:', this.method);
                                console.log('Status value:', document.querySelector('input[name="status"]').value);
                                // Don't prevent default - let form submit
                            });
                        </script>
                        @else
                        <p style="background: lightblue; padding: 10px;">
                            Status bukan in_progress, saat ini: {{ $task->status }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Task Description -->
                @if($task->description)
                <div class="prose max-w-none">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                    <div class="text-gray-700 whitespace-pre-wrap">{{ $task->description }}</div>
                </div>
                @endif
            </div>

            <!-- Timer Section -->
            @if($task->assigned_to === Auth::id())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">⏱️ Time Tracking</h3>
                        <p class="text-sm text-gray-500">Track waktu kerja kamu di task ini</p>
                    </div>
                    
                    <!-- Timer Display -->
                    <div class="flex items-center space-x-4">
                        <div id="timer-display" class="text-3xl font-mono font-bold text-gray-900">
                            00:00:00
                        </div>
                        
                        <!-- Timer Controls -->
                        <div id="timer-controls">
                            <button id="start-timer-btn" onclick="startTimer()" 
                                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm">
                                ▶️ Start Timer
                            </button>
                            <button id="stop-timer-btn" onclick="showStopTimerModal()" 
                                    class="hidden px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm">
                                ⏹️ Stop Timer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Total Time Spent -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Waktu yang Dihabiskan</p>
                                <p id="total-time-display" class="text-2xl font-bold text-gray-900">
                                    {{ $task->formatted_total_time ?? '00:00:00' }}
                                </p>
                            </div>
                        </div>
                        <button onclick="showTimerHistory()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                            📊 Lihat History
                        </button>
                    </div>
                </div>

                <!-- Timer Status Alert -->
                <div id="timer-status-alert" class="hidden mb-4"></div>
            </div>
            @endif

            <!-- Status Transition Controls -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Aksi</h3>
                
                <!-- Current Status Display -->
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status Saat Ini:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($task->status === 'todo') bg-gray-200 text-gray-800
                            @elseif($task->status === 'in_progress') bg-blue-200 text-blue-800
                            @elseif($task->status === 'review') bg-purple-200 text-purple-800
                            @else bg-green-200 text-green-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                </div>

                <!-- Rejection Reason Display -->
                @if($task->rejection_reason)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700 mt-1">{{ $task->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div id="transition-buttons" class="space-y-2">
                    <!-- Mark Complete Button (User Only - in_progress → review) -->
                    @if($task->assigned_to === Auth::id() && $task->status === 'in_progress')
                    <button onclick="markTaskComplete()" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium shadow-sm flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tandai Selesai (Kirim ke Review)</span>
                    </button>
                    @endif

                    <!-- Leader Actions (review status only) -->
                    @if($task->status === 'review')
                        @php
                            $isLeader = $task->project->leader_id === Auth::id();
                        @endphp
                        @if($isLeader)
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Approve Button -->
                            <button onclick="approveTask()" class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Setujui</span>
                            </button>

                            <!-- Reject Button -->
                            <button onclick="showRejectModal()" class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Tolak</span>
                            </button>
                        </div>
                        @else
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">⏳ Task sedang menunggu review dari project leader</p>
                        </div>
                        @endif
                    @endif

                    <!-- Done Status Message -->
                    @if($task->status === 'done')
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">✅ Task ini telah selesai dan disetujui</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Subtasks Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Subtasks (Checklist)</h3>
                        <p class="text-sm text-gray-500">Bagi task ini menjadi langkah-langkah kecil</p>
                    </div>
                    @if($task->assigned_to === Auth::id())
                    <button onclick="toggleSubtaskForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        + Tambah Subtask
                    </button>
                    @endif
                </div>

                <!-- Progress Bar -->
                @php
                    $totalSubtasks = $task->subtasks->count();
                    $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                    $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                @endphp
                
                @if($totalSubtasks > 0)
                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-gray-600">Progress</span>
                        <span class="font-medium text-gray-900" id="progress-text">{{ $completedSubtasks }} / {{ $totalSubtasks }} ({{ $progress }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="progress-bar" class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                @endif

                <!-- Add Subtask Form -->
                @if($task->assigned_to === Auth::id())
                <div id="subtask-form" class="mb-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                    <form onsubmit="createSubtask(event)" class="space-y-3">
                        <div>
                            <input type="text" id="subtask-title" placeholder="Judul subtask..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <textarea id="subtask-description" placeholder="Deskripsi (opsional)..." rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        </div>
                        <div>
                            <select id="subtask-priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="low">🟢 Low Priority</option>
                                <option value="medium" selected>🟡 Medium Priority</option>
                                <option value="high">🔴 High Priority</option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Simpan
                            </button>
                            <button type="button" onclick="toggleSubtaskForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Subtasks List -->
                <div id="subtasks-list" class="space-y-2">
                    @forelse($task->subtasks()->orderBy('priority', 'desc')->orderBy('is_completed')->get() as $subtask)
                    <div class="subtask-item flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors {{ $subtask->is_completed ? 'bg-gray-50' : '' }}" 
                         data-subtask-id="{{ $subtask->subtask_id }}">
                        <!-- Checkbox -->
                        @if($task->assigned_to === Auth::id())
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" 
                                   {{ $subtask->is_completed ? 'checked' : '' }}
                                   onchange="toggleSubtaskComplete({{ $subtask->subtask_id }})"
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        </div>
                        @else
                        <div class="flex-shrink-0 mt-0.5">
                            @if($subtask->is_completed)
                                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        @endif

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start space-x-2">
                                <!-- Priority Badge -->
                                @php
                                    $priorityColor = match($subtask->priority) {
                                        'high' => 'bg-red-100 text-red-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'low' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $priorityIcon = match($subtask->priority) {
                                        'high' => '🔴',
                                        'medium' => '🟡',
                                        'low' => '🟢',
                                        default => '⚪',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $priorityColor }} flex-shrink-0">
                                    {{ $priorityIcon }} {{ strtoupper($subtask->priority) }}
                                </span>
                                
                                <!-- Title -->
                                <p class="text-gray-900 font-medium {{ $subtask->is_completed ? 'line-through text-gray-500' : '' }} break-words">
                                    {{ $subtask->title }}
                                </p>
                            </div>
                            
                            @if($subtask->description)
                                <p class="text-sm text-gray-500 mt-1 {{ $subtask->is_completed ? 'line-through' : '' }}">{{ $subtask->description }}</p>
                            @endif
                            
                            @if($subtask->is_completed && $subtask->completed_at)
                                <p class="text-xs text-green-600 mt-1">✓ Selesai {{ $subtask->completed_at->diffForHumans() }}</p>
                            @endif

                            <!-- Start Work Section - Only for Designers/Developers -->
                            @if(in_array(Auth::user()->role, ['user']) && $task->assigned_to === Auth::id() && !$subtask->is_completed)
                                @php
                                    // Check if there's an active timer for this subtask
                                    $activeTimer = \App\Models\TimeLog::where('task_id', $task->task_id)
                                        ->where('user_id', Auth::id())
                                        ->whereNull('end_time')
                                        ->first();
                                    
                                    // Check if user is member of this project with designer/developer role
                                    $projectMember = \App\Models\ProjectMember::where('project_id', $task->project_id)
                                        ->where('user_id', Auth::id())
                                        ->whereIn('role', ['designer', 'developer'])
                                        ->first();
                                @endphp
                                
                                @if($projectMember)
                                <div class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-dashed border-blue-200 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                                                @if($projectMember->role === 'designer')
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800">
                                                    @if($projectMember->role === 'designer')
                                                        🎨 Design Work
                                                    @else
                                                        💻 Development Work
                                                    @endif
                                                </h4>
                                                <p class="text-xs text-gray-600">
                                                    @if($activeTimer)
                                                        ⏱️ Timer aktif - {{ $activeTimer->start_time->diffForHumans() }}
                                                    @else
                                                        Mulai bekerja pada subtask ini
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if($activeTimer)
                                                <!-- Stop Work Button -->
                                                <button onclick="stopWork({{ $subtask->subtask_id }})" 
                                                        class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 hover:shadow-xl flex items-center space-x-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10l6 6m0-6l-6 6"></path>
                                                    </svg>
                                                    <span>Stop Work</span>
                                                </button>
                                            @else
                                                <!-- Start Work Button -->
                                                <button onclick="startWork({{ $subtask->subtask_id }})" 
                                                        class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-sm font-bold rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 hover:shadow-xl flex items-center space-x-2 pulse-animation">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2 2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2z"></path>
                                                    </svg>
                                                    <span>🚀 START WORK</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endif
                        </div>

                        <!-- Actions -->
                        @if($task->assigned_to === Auth::id())
                        <div class="flex-shrink-0 flex space-x-1">
                            <button onclick="editSubtask({{ $subtask->subtask_id }}, '{{ addslashes($subtask->title) }}', '{{ addslashes($subtask->description ?? '') }}', '{{ $subtask->priority }}')" 
                                    class="p-1 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteSubtask({{ $subtask->subtask_id }})" 
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-gray-500">Belum ada subtask.</p>
                        @if($task->assigned_to === Auth::id())
                        <p class="text-sm text-gray-400 mt-1">Klik "Tambah Subtask" untuk memulai checklist.</p>
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Diskusi & Komentar</h3>
                        <p class="text-sm text-gray-500">Komunikasi dengan tim tentang task ini</p>
                    </div>
                    <span id="comments-count" class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">0</span>
                </div>

                <!-- Add Comment Form -->
                <div class="mb-6">
                    <form onsubmit="addComment(event)" class="space-y-3">
                        <div>
                            <textarea id="comment-input" rows="3" placeholder="Tulis komentar atau diskusi..." 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" 
                                      required></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-500">💡 Tips: Gunakan "@" untuk mention anggota tim</p>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                <span>Kirim Komentar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Comments List -->
                <div id="comments-list" class="space-y-4">
                    <!-- Comments will be loaded here via AJAX -->
                    <div class="text-center py-8">
                        <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto"></div>
                        <p class="text-gray-500 mt-3">Memuat komentar...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="comments-empty" class="hidden text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">Belum ada komentar</p>
                    <p class="text-sm text-gray-400 mt-1">Jadilah yang pertama berkomentar di task ini</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Task Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Project</span>
                        <p class="text-sm text-gray-900">{{ $task->board->project->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Board</span>
                        <p class="text-sm text-gray-900">{{ $task->board->name }}</p>
                    </div>
                    @if($task->due_date)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Due Date</span>
                        <p class="text-sm {{ $task->due_date < now() && $task->status !== 'done' ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                            {{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y g:i A') }}
                            @if($task->due_date < now() && $task->status !== 'done')
                                <span class="text-red-500">(Overdue)</span>
                            @endif
                        </p>
                    </div>
                    @endif
                    <div>
                        <span class="text-sm font-medium text-gray-500">Created</span>
                        <p class="text-sm text-gray-900">{{ $task->created_at->format('M j, Y') }}</p>
                    </div>
                    @if($task->updated_at != $task->created_at)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Last Updated</span>
                        <p class="text-sm text-gray-900">{{ $task->updated_at->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Team -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Team</h3>
                <div class="space-y-3">
                    @foreach($task->assignments as $assignment)
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-gray-600">
                                {{ substr($assignment->user->name, 0, 2) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $assignment->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->user->email }}</p>
                        </div>
                        @if($assignment->user->user_id === Auth::id())
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">You</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Leader Tools: Assign & Priority -->
            @if(isset($projectRole) && ($projectRole === 'project_manager' || auth()->user()->role === 'admin'))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Leader Tools</h3>
                <!-- Update Priority -->
                <form method="POST" action="{{ route('tasks.update-priority', $task->card_id) }}" class="mb-4">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <div class="flex items-center space-x-2">
                        <select name="priority" class="border rounded-lg px-3 py-2 text-sm">
                            <option value="high" {{ $task->priority==='high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $task->priority==='medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $task->priority==='low' ? 'selected' : '' }}>Low</option>
                        </select>
                        <button type="submit" class="px-3 py-2 text-sm bg-indigo-600 text-white rounded-md">Update</button>
                    </div>
                </form>

                <!-- Assign Members -->
                @if(isset($projectMembers) && $projectMembers->count() > 0)
                <form method="POST" action="{{ route('tasks.assign-members', $task->card_id) }}">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign to</label>
                    <select multiple name="user_ids[]" class="w-full border rounded-lg px-3 py-2 text-sm h-32">
                        @foreach($projectMembers as $member)
                            <option value="{{ $member->user_id }}">
                                {{ $member->user->full_name }} ({{ $member->role }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk pilih lebih dari satu.</p>
                    <button type="submit" class="mt-2 px-3 py-2 text-sm bg-blue-600 text-white rounded-md">Assign</button>
                </form>
                @endif

                <div class="mt-4 p-3 rounded bg-yellow-50 text-yellow-800 text-xs">
                    Tip: Ketik "BLOCKER: <pesan>" pada progress untuk menandai hambatan. Komentar akan ditandai otomatis.
                </div>
            </div>
            @endif

            <!-- Your Role Context -->
            @if($projectRole)
            <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Your Role in This Project</h4>
                <div class="flex items-center space-x-2">
                    @if($projectRole === 'project_manager')
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">📋 Project Manager</span>
                    @elseif($projectRole === 'developer')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">🖥️ Developer</span>
                    @elseif($projectRole === 'designer')
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">🎨 Designer</span>
                    @endif
                </div>
                <p class="text-xs text-blue-700 mt-2">
                    @if($projectRole === 'project_manager')
                        You can manage this task and its assignments.
                    @elseif($isAssigned)
                        This task is assigned to you.
                    @else
                        You have project access as a {{ str_replace('_', ' ', $projectRole) }}.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Stop Timer Modal -->
<div id="stop-timer-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⏹️ Stop Timer</h3>
        <form onsubmit="stopTimer(event)">
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Waktu yang telah berjalan:</p>
                <p id="stop-modal-elapsed" class="text-3xl font-mono font-bold text-gray-900 mb-4">00:00:00</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                    <span class="text-gray-500 font-normal">- Apa yang sudah dikerjakan?</span>
                </label>
                <textarea id="timer-notes" rows="3" placeholder="Contoh: Implementasi fitur login, fix bug pada dashboard..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Stop Timer
                </button>
                <button type="button" onclick="closeStopTimerModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Timer History Modal -->
<div id="history-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">📊 Timer History</h3>
            <button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- History Summary -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Total Waktu</p>
                    <p id="history-total-time" class="text-xl font-bold text-gray-900">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Sessions</p>
                    <p id="history-session-count" class="text-xl font-bold text-gray-900">-</p>
                </div>
            </div>
        </div>
        
        <!-- History List -->
        <div id="history-list" class="space-y-3">
            <div class="text-center py-8 text-gray-500">
                Loading...
            </div>
        </div>
    </div>
</div>

<!-- Edit Subtask Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Subtask</h3>
        <form onsubmit="updateSubtask(event)">
            <input type="hidden" id="edit-subtask-id">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input type="text" id="edit-subtask-title" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="edit-subtask-description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select id="edit-subtask-priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="low">🟢 Low Priority</option>
                        <option value="medium">🟡 Medium Priority</option>
                        <option value="high">🔴 High Priority</option>
                    </select>
                </div>
                <div class="flex space-x-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Task Modal -->
<div id="reject-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tolak Task</h3>
        <p class="text-sm text-gray-600 mb-4">Berikan alasan mengapa task ini ditolak. Task akan dikembalikan ke status "In Progress".</p>
        <form onsubmit="rejectTask(event)">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan *</label>
                    <textarea id="rejection-reason" rows="4" placeholder="Jelaskan apa yang perlu diperbaiki..." 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none" required></textarea>
                </div>
                <div class="flex space-x-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Tolak Task
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Extension Request Modal -->
<div id="extensionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Request Deadline Extension</h3>
            <button onclick="closeExtensionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="extensionForm" onsubmit="submitExtensionRequest(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Current Deadline
                </label>
                <p class="text-sm text-red-600 font-semibold">
                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y, H:i') : 'No deadline set' }}
                </p>
            </div>

            <div class="mb-4">
                <label for="requested_deadline" class="block text-sm font-medium text-gray-700 mb-2">
                    Requested New Deadline <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="requested_deadline" 
                    name="requested_deadline" 
                    required
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Extension <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="reason" 
                    name="reason" 
                    rows="4"
                    required
                    minlength="10"
                    maxlength="500"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Please explain why you need a deadline extension (minimum 10 characters)..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="charCount">0</span>/500 characters (minimum 10)
                </p>
            </div>

            <div id="extensionErrorMessage" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button 
                    type="button" 
                    onclick="closeExtensionModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-lg transition-all duration-200"
                >
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Extension Request Modal -->
<div id="rejectExtensionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Reject Extension Request</h3>
            <button onclick="closeRejectExtensionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="rejectExtensionForm" onsubmit="submitRejectExtensionRequest(event)">
            <input type="hidden" id="rejectRequestId" name="request_id">
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-3">
                    Mohon berikan alasan penolakan yang jelas agar developer memahami keputusan Anda.
                </p>
                
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="rejection_reason" 
                    name="rejection_reason" 
                    rows="4"
                    required
                    minlength="10"
                    maxlength="500"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Contoh: Task ini sudah terlambat 2 minggu, tidak dapat diperpanjang lagi..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="rejectCharCount">0</span>/500 characters (minimum 10)
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button 
                    type="button" 
                    onclick="closeRejectExtensionModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200"
                >
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const taskId = {{ $task->task_id }};
const csrfToken = '{{ csrf_token() }}';

// ========== TIMER FUNCTIONALITY ==========
let timerInterval = null;
let timerStartTime = null;
let isTimerRunning = false;

// Check timer status on page load
document.addEventListener('DOMContentLoaded', function() {
    checkTimerStatus();
});

// Check if timer is running
async function checkTimerStatus() {
    try {
        const response = await fetch(`/tasks/${taskId}/timer/status`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.is_running) {
            // Timer is running
            timerStartTime = new Date(data.start_time);
            isTimerRunning = true;
            startTimerDisplay();
            
            // Show stop button, hide start button
            document.getElementById('start-timer-btn').classList.add('hidden');
            document.getElementById('stop-timer-btn').classList.remove('hidden');
            
            showStatusAlert('Timer sedang berjalan sejak ' + formatDateTime(timerStartTime), 'info');
        }
    } catch (error) {
        console.error('Error checking timer status:', error);
    }
}

// Start timer
async function startTimer() {
    try {
        const response = await fetch(`/tasks/${taskId}/timer/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            timerStartTime = new Date();
            isTimerRunning = true;
            startTimerDisplay();
            
            // Switch buttons
            document.getElementById('start-timer-btn').classList.add('hidden');
            document.getElementById('stop-timer-btn').classList.remove('hidden');
            
            showNotification('Timer dimulai! 🚀', 'success');
            showStatusAlert('Timer dimulai pada ' + formatDateTime(timerStartTime), 'success');
            
            // Update task status badge if changed
            if (data.task_status) {
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showNotification(data.message, 'error');
            
            // If there's a running task, show option to navigate
            if (data.running_task_id) {
                if (confirm(data.message + '\n\nBuka task tersebut?')) {
                    window.location.href = `/tasks/${data.running_task_id}`;
                }
            }
        }
    } catch (error) {
        console.error('Error starting timer:', error);
        showNotification('Gagal memulai timer', 'error');
    }
}

// Start timer display (update every second)
function startTimerDisplay() {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    timerInterval = setInterval(() => {
        if (timerStartTime) {
            const elapsed = Math.floor((new Date() - timerStartTime) / 1000);
            document.getElementById('timer-display').textContent = formatSeconds(elapsed);
        }
    }, 1000);
}

// Show stop timer modal
function showStopTimerModal() {
    if (timerStartTime) {
        const elapsed = Math.floor((new Date() - timerStartTime) / 1000);
        document.getElementById('stop-modal-elapsed').textContent = formatSeconds(elapsed);
    }
    
    const modal = document.getElementById('stop-timer-modal');
    modal.style.display = 'flex';
    document.getElementById('timer-notes').focus();
}

// Close stop timer modal
function closeStopTimerModal() {
    const modal = document.getElementById('stop-timer-modal');
    modal.style.display = 'none';
    document.getElementById('timer-notes').value = '';
}

// Stop timer
async function stopTimer(event) {
    event.preventDefault();
    
    const notes = document.getElementById('timer-notes').value;
    
    try {
        const response = await fetch(`/tasks/${taskId}/timer/stop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ notes })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Stop timer display
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            
            isTimerRunning = false;
            timerStartTime = null;
            
            // Reset display
            document.getElementById('timer-display').textContent = '00:00:00';
            
            // Switch buttons
            document.getElementById('start-timer-btn').classList.remove('hidden');
            document.getElementById('stop-timer-btn').classList.add('hidden');
            
            // Update total time
            document.getElementById('total-time-display').textContent = data.formatted_total_time;
            
            closeStopTimerModal();
            showNotification(`Timer dihentikan! Durasi: ${data.duration}`, 'success');
            showStatusAlert(`Sesi selesai: ${data.duration}`, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error stopping timer:', error);
        showNotification('Gagal menghentikan timer', 'error');
    }
}

// Show timer history
async function showTimerHistory() {
    const modal = document.getElementById('history-modal');
    modal.style.display = 'flex';
    
    document.getElementById('history-list').innerHTML = '<div class="text-center py-8 text-gray-500">Loading...</div>';
    
    try {
        const response = await fetch(`/tasks/${taskId}/timer/history`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update summary
            document.getElementById('history-total-time').textContent = data.total_time_formatted;
            document.getElementById('history-session-count').textContent = data.log_count + ' sessions';
            
            // Render history list
            if (data.time_logs.length === 0) {
                document.getElementById('history-list').innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>Belum ada history timer</p>
                    </div>
                `;
            } else {
                let html = '';
                data.time_logs.forEach(log => {
                    const duration = formatSeconds(log.duration_seconds);
                    const startDate = new Date(log.start_time);
                    const endDate = log.end_time ? new Date(log.end_time) : null;
                    
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">${duration}</p>
                                    <p class="text-sm text-gray-500">
                                        ${formatDateTime(startDate)} - ${endDate ? formatDateTime(endDate) : 'Running'}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                    ${log.user.full_name}
                                </span>
                            </div>
                            ${log.notes ? `<p class="text-sm text-gray-700 mt-2 bg-gray-50 p-2 rounded">${log.notes}</p>` : ''}
                        </div>
                    `;
                });
                document.getElementById('history-list').innerHTML = html;
            }
        } else {
            showNotification(data.message, 'error');
            closeHistoryModal();
        }
    } catch (error) {
        console.error('Error loading history:', error);
        document.getElementById('history-list').innerHTML = '<div class="text-center py-8 text-red-500">Gagal memuat history</div>';
    }
}

// Close history modal
function closeHistoryModal() {
    const modal = document.getElementById('history-modal');
    modal.style.display = 'none';
}

// Format seconds to HH:MM:SS
function formatSeconds(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

// Format date time
function formatDateTime(date) {
    return date.toLocaleString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// Show status alert
function showStatusAlert(message, type = 'info') {
    const alertDiv = document.getElementById('timer-status-alert');
    if (!alertDiv) return;
    
    const colors = {
        info: 'bg-blue-50 border-blue-200 text-blue-800',
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800'
    };
    
    alertDiv.className = `border rounded-lg p-3 ${colors[type]}`;
    alertDiv.textContent = message;
    alertDiv.classList.remove('hidden');
}

// ========== SUBTASK FUNCTIONALITY ==========

// Toggle form visibility
function toggleSubtaskForm() {
    const form = document.getElementById('subtask-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') {
        document.getElementById('subtask-title').focus();
    } else {
        // Reset form
        document.getElementById('subtask-title').value = '';
        document.getElementById('subtask-description').value = '';
        document.getElementById('subtask-priority').value = 'medium';
    }
}

// Create new subtask
async function createSubtask(event) {
    event.preventDefault();
    
    const title = document.getElementById('subtask-title').value;
    const description = document.getElementById('subtask-description').value;
    const priority = document.getElementById('subtask-priority').value;
    
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description, priority })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showNotification('Subtask berhasil ditambahkan!', 'success');
            
            // Reload page to show new subtask
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message || 'Gagal menambahkan subtask', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan subtask', 'error');
    }
}

// Toggle subtask completion
async function toggleSubtaskComplete(subtaskId) {
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update progress bar
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            if (progressBar && data.statistics) {
                progressBar.style.width = data.statistics.progress + '%';
                progressText.textContent = `${data.statistics.completed} / ${data.statistics.total} (${data.statistics.progress}%)`;
            }
            
            // Update subtask item appearance
            const subtaskItem = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
            if (subtaskItem) {
                if (data.subtask.is_completed) {
                    subtaskItem.classList.add('bg-gray-50');
                    subtaskItem.querySelector('.text-gray-900').classList.add('line-through', 'text-gray-500');
                } else {
                    subtaskItem.classList.remove('bg-gray-50');
                    subtaskItem.querySelector('.text-gray-900').classList.remove('line-through', 'text-gray-500');
                }
            }
            
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Gagal update status subtask', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat update status', 'error');
    }
}

// Open edit modal
function editSubtask(subtaskId, title, description, priority) {
    document.getElementById('edit-subtask-id').value = subtaskId;
    document.getElementById('edit-subtask-title').value = title;
    document.getElementById('edit-subtask-description').value = description;
    document.getElementById('edit-subtask-priority').value = priority;
    const modal = document.getElementById('edit-modal');
    modal.style.display = 'flex';
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.style.display = 'none';
}

// Update subtask
async function updateSubtask(event) {
    event.preventDefault();
    
    const subtaskId = document.getElementById('edit-subtask-id').value;
    const title = document.getElementById('edit-subtask-title').value;
    const description = document.getElementById('edit-subtask-description').value;
    const priority = document.getElementById('edit-subtask-priority').value;
    
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description, priority })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Subtask berhasil diupdate!', 'success');
            closeEditModal();
            
            // Reload page to show updated subtask
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message || 'Gagal update subtask', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat update subtask', 'error');
    }
}

// Delete subtask
async function deleteSubtask(subtaskId) {
    if (!confirm('Apakah Anda yakin ingin menghapus subtask ini?')) {
        return;
    }
    
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Subtask berhasil dihapus!', 'success');
            
            // Remove subtask from DOM
            const subtaskItem = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
            if (subtaskItem) {
                subtaskItem.style.opacity = '0';
                setTimeout(() => subtaskItem.remove(), 300);
            }
            
            // Reload page after short delay to update progress
            setTimeout(() => location.reload(), 800);
        } else {
            showNotification(data.message || 'Gagal menghapus subtask', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus subtask', 'error');
    }
}

// Show notification toast
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Close modal when clicking outside
document.getElementById('edit-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// ===== Status Transition Functions =====

// Mark task as complete (user action: in_progress → review)
async function markTaskComplete() {
    if (!confirm('Apakah Anda yakin ingin menyelesaikan task ini dan mengirimnya untuk review?')) {
        return;
    }

    try {
        const response = await fetch(`/tasks/${taskId}/transitions/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Task berhasil dikirim untuk review!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Gagal menyelesaikan task', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menyelesaikan task', 'error');
    }
}

// Approve task (leader action: review → done)
async function approveTask() {
    if (!confirm('Apakah Anda yakin ingin menyetujui task ini?')) {
        return;
    }

    try {
        const response = await fetch(`/tasks/${taskId}/transitions/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Task berhasil disetujui dan ditandai selesai!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Gagal menyetujui task', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menyetujui task', 'error');
    }
}

// Show reject modal
function showRejectModal() {
    const modal = document.getElementById('reject-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Close reject modal
function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('rejection-reason').value = '';
    }
}

// Reject task (leader action: review → in_progress)
async function rejectTask(event) {
    event.preventDefault();
    
    const reason = document.getElementById('rejection-reason').value.trim();
    
    if (!reason) {
        showNotification('Alasan penolakan harus diisi', 'error');
        return;
    }

    try {
        const response = await fetch(`/tasks/${taskId}/transitions/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ reason })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Task berhasil ditolak dan dikembalikan ke in progress', 'success');
            closeRejectModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Gagal menolak task', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menolak task', 'error');
    }
}

// ===== Comments Functions =====

// Load comments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadComments();
});

// Load all comments for this task
async function loadComments() {
    try {
        const response = await fetch(`/tasks/${taskId}/comments`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            displayComments(data.comments);
            updateCommentsCount(data.total);
        } else {
            console.error('Failed to load comments');
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        document.getElementById('comments-list').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-500">Gagal memuat komentar</p>
            </div>
        `;
    }
}

// Display comments in the list
function displayComments(comments) {
    const commentsList = document.getElementById('comments-list');
    const emptyState = document.getElementById('comments-empty');

    if (comments.length === 0) {
        commentsList.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }

    commentsList.classList.remove('hidden');
    emptyState.classList.add('hidden');

    commentsList.innerHTML = comments.map(comment => `
        <div class="flex space-x-3 group" data-comment-id="${comment.comment_id}">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <span class="text-sm font-bold text-white">${comment.user.initials}</span>
                </div>
            </div>
            
            <!-- Comment Content -->
            <div class="flex-1 min-w-0">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold text-gray-900">${comment.user.name}</span>
                        ${comment.is_owner ? `
                            <button onclick="deleteComment(${comment.comment_id})" 
                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-red-600" 
                                    title="Hapus komentar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                    <p class="text-gray-700 text-sm whitespace-pre-wrap break-words">${escapeHtml(comment.comment)}</p>
                </div>
                <div class="flex items-center space-x-2 mt-1 px-3">
                    <span class="text-xs text-gray-500" title="${comment.created_at}">${comment.created_at_human}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// Add new comment
async function addComment(event) {
    event.preventDefault();

    const commentInput = document.getElementById('comment-input');
    const comment = commentInput.value.trim();

    if (!comment) {
        showNotification('Komentar tidak boleh kosong', 'error');
        return;
    }

    try {
        const response = await fetch(`/tasks/${taskId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ comment })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Komentar berhasil ditambahkan', 'success');
            commentInput.value = '';
            loadComments(); // Reload comments
        } else {
            showNotification(data.error || 'Gagal menambahkan komentar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan komentar', 'error');
    }
}

// Delete comment
async function deleteComment(commentId) {
    if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
        return;
    }

    try {
        const response = await fetch(`/tasks/${taskId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Komentar berhasil dihapus', 'success');
            loadComments(); // Reload comments
        } else {
            showNotification(data.error || 'Gagal menghapus komentar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus komentar', 'error');
    }
}

// Update comments count badge
function updateCommentsCount(count) {
    const badge = document.getElementById('comments-count');
    if (badge) {
        badge.textContent = count;
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Start Work on Subtask
async function startWork(subtaskId) {
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}/start-timer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('🚀 Timer started! Selamat bekerja!', 'success');
            // Update UI dynamically instead of full page reload
            updateSubtaskUI(subtaskId, 'active');
        } else {
            showNotification(data.error || 'Gagal memulai timer', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memulai timer', 'error');
    }
}

// Stop Work on Subtask
async function stopWork(subtaskId) {
    try {
        const response = await fetch(`/tasks/${taskId}/subtasks/${subtaskId}/stop-timer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            const timeSpent = data.timeSpent || 'Unknown';
            showNotification(`⏰ Timer stopped! Time spent: ${timeSpent}`, 'success');
            // Update UI dynamically instead of full page reload
            updateSubtaskUI(subtaskId, 'stopped');
        } else {
            showNotification(data.error || 'Gagal menghentikan timer', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghentikan timer', 'error');
    }
}

// Update subtask UI without page reload
function updateSubtaskUI(subtaskId, status) {
    const subtaskEl = document.querySelector(`[data-subtask-id="${subtaskId}"]`);
    if (!subtaskEl) return;
    
    const startBtn = subtaskEl.querySelector('[onclick^="startWork"]');
    const stopBtn = subtaskEl.querySelector('[onclick^="stopWork"]');
    
    if (status === 'active' && startBtn && stopBtn) {
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-flex';
    } else if (status === 'stopped' && startBtn && stopBtn) {
        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
    }
}

// ========== EXTENSION REQUEST FUNCTIONALITY ==========
function openExtensionModal() {
    document.getElementById('extensionModal').classList.remove('hidden');
    document.getElementById('reason').value = '';
    document.getElementById('requested_deadline').value = '';
    document.getElementById('charCount').textContent = '0';
    document.getElementById('extensionErrorMessage').classList.add('hidden');
}

function closeExtensionModal() {
    document.getElementById('extensionModal').classList.add('hidden');
}

// Character counter for reason textarea
document.addEventListener('DOMContentLoaded', function() {
    const reasonTextarea = document.getElementById('reason');
    const charCount = document.getElementById('charCount');
    
    if (reasonTextarea && charCount) {
        reasonTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});

async function submitExtensionRequest(event) {
    event.preventDefault();
    
    const reason = document.getElementById('reason').value;
    const requestedDeadline = document.getElementById('requested_deadline').value;
    
    if (reason.length < 10) {
        showExtensionError('Reason must be at least 10 characters');
        return;
    }

    if (!requestedDeadline) {
        showExtensionError('Please select a new deadline');
        return;
    }

    try {
        const response = await fetch('/extension-requests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                card_id: {{ $task->card_id }},
                reason: reason,
                requested_deadline: requestedDeadline
            })
        });

        const data = await response.json();

        if (data.success) {
            closeExtensionModal();
            showNotification('Extension request submitted successfully! Your leader will review it.', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showExtensionError(data.message || 'Failed to submit extension request');
        }
    } catch (error) {
        console.error('Error:', error);
        showExtensionError('An error occurred while submitting the request');
    }
}

function showExtensionError(message) {
    const errorDiv = document.getElementById('extensionErrorMessage');
    errorDiv.querySelector('p').textContent = message;
    errorDiv.classList.remove('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeExtensionModal();
    }
});

// Close modal on outside click
const extensionModal = document.getElementById('extensionModal');
if (extensionModal) {
    extensionModal.addEventListener('click', function(event) {
        if (event.target === this) {
            closeExtensionModal();
        }
    });
}

// ========================================
// Extension Request Approval/Rejection (Leader)
// ========================================

async function approveExtensionRequest(requestId) {
    if (!confirm('Apakah Anda yakin ingin menyetujui perpanjangan deadline ini?')) {
        return;
    }

    try {
        const response = await fetch(`/extension-requests/${requestId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Extension request approved! Task deadline updated.', 'success');
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to approve extension request', 'error');
        }
    } catch (error) {
        console.error('Error approving extension request:', error);
        showNotification('An error occurred while approving the extension request', 'error');
    }
}

function showRejectExtensionModal(requestId) {
    document.getElementById('rejectRequestId').value = requestId;
    document.getElementById('rejection_reason').value = '';
    document.getElementById('rejectCharCount').textContent = '0';
    document.getElementById('rejectExtensionModal').classList.remove('hidden');
}

function closeRejectExtensionModal() {
    document.getElementById('rejectExtensionModal').classList.add('hidden');
}

async function submitRejectExtensionRequest(event) {
    event.preventDefault();
    
    const requestId = document.getElementById('rejectRequestId').value;
    const reason = document.getElementById('rejection_reason').value.trim();
    
    if (!reason || reason.length < 10) {
        showNotification('Alasan penolakan harus minimal 10 karakter', 'error');
        return;
    }

    await rejectExtensionRequest(requestId, reason);
}

async function rejectExtensionRequest(requestId, reason) {
    try {
        const response = await fetch(`/extension-requests/${requestId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Extension request rejected', 'success');
            closeRejectExtensionModal();
            // Reload page to remove the request section
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to reject extension request', 'error');
        }
    } catch (error) {
        console.error('Error rejecting extension request:', error);
        showNotification('An error occurred while rejecting the extension request', 'error');
    }
}

// Character counter for rejection reason
const rejectReasonTextarea = document.getElementById('rejection_reason');
if (rejectReasonTextarea) {
    rejectReasonTextarea.addEventListener('input', function() {
        document.getElementById('rejectCharCount').textContent = this.value.length;
    });
}

// Close reject modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRejectExtensionModal();
    }
});

// Close reject modal on outside click
const rejectExtensionModal = document.getElementById('rejectExtensionModal');
if (rejectExtensionModal) {
    rejectExtensionModal.addEventListener('click', function(event) {
        if (event.target === this) {
            closeRejectExtensionModal();
        }
    });
}
</script>

@push('styles')
<style>
/* Pulse animation for Start Work button */
.pulse-animation {
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

/* Gradient hover effects */
.start-work-section {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%);
    border: 2px dashed #3b82f6;
    transition: all 0.3s ease;
}

.start-work-section:hover {
    background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
    border-color: #2563eb;
    transform: translateY(-1px);
}

/* Shine effect for active timer */
.active-timer {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #f59e0b;
    position: relative;
    overflow: hidden;
}

.active-timer::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>
@endpush