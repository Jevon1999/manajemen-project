@extends('layouts.app')

@section('title', 'Extension Requests')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Extension Requests</h1>
        <p class="text-gray-600 mt-1">Review and manage deadline extension requests from your team</p>
    </div>

    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-6xl mb-4">✅</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Pending Extension Requests</h3>
            <p class="text-gray-600">All extension requests have been reviewed!</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($requests as $request)
                <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Card/Task Info -->
                                <div class="mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            @if($request->entity_type === 'task' && $request->task)
                                                {{ $request->task->title }}
                                            @else
                                                {{ $request->card->card_name }}
                                            @endif
                                        </h3>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            ⏰ Pending
                                        </span>
                                        @if($request->entity_type === 'task')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                📋 Task
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                🎯 Card
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Project: <span class="font-medium">
                                            @if($request->entity_type === 'task' && $request->task)
                                                {{ $request->task->project->project_name }}
                                            @else
                                                {{ $request->card->board->project->project_name }}
                                            @endif
                                        </span>
                                    </p>
                                </div>

                                <!-- Requester Info -->
                                <div class="flex items-center gap-2 mb-4">
                                    <img 
                                        src="{{ $request->requester->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($request->requester->name) }}" 
                                        alt="{{ $request->requester->name }}"
                                        class="w-8 h-8 rounded-full"
                                    >
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $request->requester->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                <!-- Deadline Info -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <p class="text-xs text-gray-600 mb-1">Current Deadline</p>
                                        <p class="text-sm font-semibold text-red-600">
                                            {{ $request->old_deadline->format('d M Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            ({{ $request->old_deadline->diffForHumans() }})
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-3">
                                        <p class="text-xs text-gray-600 mb-1">Requested Deadline</p>
                                        <p class="text-sm font-semibold text-green-600">
                                            {{ $request->requested_deadline->format('d M Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            (+{{ $request->getExtensionDays() }} days extension)
                                        </p>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                    <p class="text-xs font-semibold text-blue-900 mb-2">Reason for Extension:</p>
                                    <p class="text-sm text-blue-800">{{ $request->reason }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-2 ml-6">
                                <button 
                                    onclick="approveRequest({{ $request->id }})"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Approve
                                </button>
                                <button 
                                    onclick="openRejectModal({{ $request->id }}, '{{ $request->entity_type === 'task' && $request->task ? $request->task->title : $request->card->card_name }}')"
                                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors flex items-center gap-2"
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
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Reject Extension Request</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            You are about to reject the extension request for: <strong id="taskName"></strong>
        </p>

        <form id="rejectForm" onsubmit="submitReject(event)">
            <input type="hidden" id="requestId" name="request_id">
            
            <div class="mb-4">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="rejection_reason" 
                    name="rejection_reason" 
                    rows="4"
                    required
                    minlength="10"
                    maxlength="500"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Please provide a clear reason for rejecting this extension request..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
            </div>

            <div id="errorMessage" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button 
                    type="button" 
                    onclick="closeRejectModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors"
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
function approveRequest(requestId) {
    if (!confirm('Are you sure you want to approve this extension request?')) {
        return;
    }

    fetch(`/extension-requests/${requestId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Extension request approved successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to approve request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while approving the request', 'error');
    });
}

function openRejectModal(requestId, taskName) {
    document.getElementById('requestId').value = requestId;
    document.getElementById('taskName').textContent = taskName;
    document.getElementById('rejection_reason').value = '';
    document.getElementById('errorMessage').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function submitReject(event) {
    event.preventDefault();
    
    const requestId = document.getElementById('requestId').value;
    const rejectionReason = document.getElementById('rejection_reason').value;
    
    if (rejectionReason.length < 10) {
        showError('Rejection reason must be at least 10 characters');
        return;
    }

    fetch(`/extension-requests/${requestId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ rejection_reason: rejectionReason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRejectModal();
            showToast('Extension request rejected', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message || 'Failed to reject request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred while rejecting the request');
    });
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.querySelector('p').textContent = message;
    errorDiv.classList.remove('hidden');
}

function showToast(message, type = 'success') {
    // Reuse existing toast system if available, or create simple alert
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        alert(message);
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRejectModal();
    }
});

// Close modal on outside click
document.getElementById('rejectModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
