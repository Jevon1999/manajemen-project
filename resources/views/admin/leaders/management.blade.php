@extends('layout.app')

@section('title', 'Team Leader Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Team Leader Management
                </h1>
                <p class="text-gray-600 mt-2">Kelola team leaders, promote users, dan assign ke projects</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="openPromoteModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Promote to Leader
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $leaders->count() }}</h3>
                    <p class="text-gray-600">Active Leaders</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $projects->count() }}</h3>
                    <p class="text-gray-600">Available Projects</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $promotableUsers->count() }}</h3>
                    <p class="text-gray-600">Promotable Users</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    @php
                        $assignedLeaders = $leaders->filter(function($leader) {
                            return $leader->projectMemberships->count() > 0;
                        })->count();
                    @endphp
                    <h3 class="text-2xl font-bold text-gray-900">{{ $assignedLeaders }}</h3>
                    <p class="text-gray-600">Assigned Leaders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button onclick="showTab('current-leaders')" id="tab-current-leaders" class="tab-button active py-4 px-6 border-b-2 border-purple-500 text-purple-600 font-medium text-sm">
                    Current Leaders
                </button>
                <button onclick="showTab('available-users')" id="tab-available-users" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Available Users
                </button>
                <button onclick="showTab('project-assignments')" id="tab-project-assignments" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Project Assignments
                </button>
            </nav>
        </div>

        <!-- Current Leaders Tab -->
        <div id="content-current-leaders" class="tab-content p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Current Team Leaders</h2>
                <div class="flex items-center space-x-3">
                    <input type="text" id="search-leaders" placeholder="Search leaders..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($leaders as $leader)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-bold text-lg">{{ substr($leader->full_name, 0, 1) }}</span>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-900">{{ $leader->full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $leader->email }}</p>
                            </div>
                        </div>
                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full">Leader</span>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Project Assignments:</h4>
                        @if($leader->projectMemberships->count() > 0)
                            <div class="space-y-1">
                                @foreach($leader->projectMemberships as $membership)
                                @if($membership->project)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ $membership->project->project_name }}</span>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ ucfirst($membership->role) }}</span>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">No project assignments</p>
                        @endif
                    </div>

                    <div class="flex space-x-2">
                        <button onclick="assignToProject({{ $leader->user_id }})" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded transition-colors">
                            Assign to Project
                        </button>
                        <button onclick="removeLeader({{ $leader->user_id }})" class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition-colors">
                            Remove Role
                        </button>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Leaders Found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by promoting a user to leader role.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Available Users Tab -->
        <div id="content-available-users" class="tab-content p-6 hidden">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Users Available for Promotion</h2>
                <div class="flex items-center space-x-3">
                    <input type="text" id="search-users" placeholder="Search users..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($promotableUsers as $user)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 font-bold text-lg">{{ substr($user->full_name, 0, 1) }}</span>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-900">{{ $user->full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded-full">User</span>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Username: {{ $user->username }}</p>
                        <p class="text-sm text-gray-600">Status: {{ ucfirst($user->status) }}</p>
                    </div>

                    <button onclick="promoteUser({{ $user->user_id }})" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded transition-colors">
                        Promote to Leader
                    </button>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Users Available</h3>
                    <p class="mt-1 text-sm text-gray-500">All users have already been promoted or are inactive.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Project Assignments Tab -->
        <div id="content-project-assignments" class="tab-content p-6 hidden">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Project Assignments</h2>
            </div>

            <div class="space-y-6">
                @forelse($projects as $project)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $project->description }}</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">{{ ucfirst($project->status) }}</span>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Assigned Leaders:</h4>
                        @php
                            $projectLeaders = $leaders->filter(function($leader) use ($project) {
                                return $leader->projectMemberships->where('project_id', $project->project_id)->count() > 0;
                            });
                        @endphp
                        
                        @if($projectLeaders->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($projectLeaders as $leader)
                                @php
                                    $membership = $leader->projectMemberships->where('project_id', $project->project_id)->first();
                                @endphp
                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full">
                                    {{ $leader->full_name }} ({{ ucfirst($membership->role) }})
                                </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">No leaders assigned</p>
                        @endif
                    </div>

                    <button onclick="selectProjectForAssignment({{ $project->project_id }}, '{{ $project->name }}')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded transition-colors">
                        Assign Leader to Project
                    </button>
                </div>
                @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Projects Found</h3>
                    <p class="mt-1 text-sm text-gray-500">Create a project first before assigning leaders.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Promote User Modal -->
<div id="promoteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Promote User to Leader</h3>
                <button onclick="closePromoteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="promoteForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select id="userSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Choose a user to promote...</option>
                        @foreach($promotableUsers as $user)
                        <option value="{{ $user->user_id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closePromoteModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Promote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign to Project Modal -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Assign Leader to Project</h3>
                <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="assignForm">
                <input type="hidden" id="leaderId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Project</label>
                    <select id="projectSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Choose a project...</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->project_id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeAssignModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-purple-500', 'text-purple-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-purple-500', 'text-purple-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

// Modal functions
function openPromoteModal() {
    document.getElementById('promoteModal').classList.remove('hidden');
    document.getElementById('promoteModal').classList.add('flex');
}

function closePromoteModal() {
    document.getElementById('promoteModal').classList.add('hidden');
    document.getElementById('promoteModal').classList.remove('flex');
}

function assignToProject(leaderId) {
    document.getElementById('leaderId').value = leaderId;
    document.getElementById('assignModal').classList.remove('hidden');
    document.getElementById('assignModal').classList.add('flex');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignModal').classList.remove('flex');
}

function selectProjectForAssignment(projectId, projectName) {
    document.getElementById('projectSelect').value = projectId;
    // You could open a leader selection modal here instead
    alert('Feature to select leader for ' + projectName + ' - implement leader selection modal');
}

// Promote user function
function promoteUser(userId) {
    if (confirm('Are you sure you want to promote this user to leader?')) {
        fetch('/api/leaders/promote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while promoting user');
        });
    }
}

// Remove leader function
function removeLeader(userId) {
    if (confirm('Are you sure you want to remove leader role from this user?')) {
        fetch('/api/leaders/demote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing leader role');
        });
    }
}

// Form submissions
document.getElementById('promoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('userSelect').value;
    if (userId) {
        promoteUser(userId);
        closePromoteModal();
    }
});

document.getElementById('assignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const leaderId = document.getElementById('leaderId').value;
    const projectId = document.getElementById('projectSelect').value;
    
    if (leaderId && projectId) {
        fetch('/api/leaders/assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                leader_id: leaderId,
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning leader');
        });
        
        closeAssignModal();
    }
});

// Search functionality
document.getElementById('search-leaders').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const leaderCards = document.querySelectorAll('#content-current-leaders .bg-gray-50');
    
    leaderCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

document.getElementById('search-users').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const userCards = document.querySelectorAll('#content-available-users .bg-gray-50');
    
    userCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
@endpush

<style>
.tab-button.active {
    border-color: #9333ea !important;
    color: #9333ea !important;
}

.tab-button:hover:not(.active) {
    color: #374151;
}
</style>
@endsection