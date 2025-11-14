@extends('layout.app')

@section('title', 'Manage Team - ' . $project->project_name)

@section('page-title', 'Manage Team')
@section('page-description', 'Manage team members for ' . $project->project_name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Project Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $project->project_name }}</h2>
                <p class="text-gray-600 mt-1">{{ $project->description ?: 'No description' }}</p>
                <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        {{ $project->members->count() }} Members
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4a1 1 0 001 1h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h3a1 1 0 001-1z"/>
                        </svg>
                        {{ ucfirst($project->status) }}
                    </span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('leader.projects') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Projects
                </a>
                <button onclick="showAddMemberModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Member
                </button>
            </div>
        </div>
    </div>

    <!-- Team Members -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Team Members</h3>
            <p class="text-sm text-gray-500">Manage team members and their roles</p>
        </div>
        
        <div id="teamMembersList" class="divide-y divide-gray-200">
            @foreach($project->members as $member)
            <div class="p-6 member-item" data-user-id="{{ $member->user_id }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700">
                            {{ strtoupper(substr($member->user->full_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $member->user->full_name }}</h4>
                            <p class="text-sm text-gray-500">{{ $member->user->email }}</p>
                            <p class="text-xs text-gray-400">Joined {{ $member->joined_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            @if($member->role === 'project_manager')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Project Manager
                                </span>
                            @else
                                <select onchange="updateMemberRole({{ $member->user_id }}, this.value)" 
                                        class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="developer" {{ $member->role === 'developer' ? 'selected' : '' }}>Developer</option>
                                    <option value="designer" {{ $member->role === 'designer' ? 'selected' : '' }}>Designer</option>
                                    <option value="tester" {{ $member->role === 'tester' ? 'selected' : '' }}>Tester</option>
                                </select>
                            @endif
                        </div>
                        
                        @if($member->role !== 'project_manager' && $member->user_id !== Auth::id())
                        <button onclick="removeMember({{ $member->user_id }}, '{{ $member->user->full_name }}')" 
                                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="addMemberForm" class="bg-white">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Add Team Member</h3>
                    <p class="mt-1 text-sm text-gray-500">Add a new member to your project team</p>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search User</label>
                        <input type="text" id="userSearch" placeholder="Search by name or email..."
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div id="searchResults" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md hidden"></div>
                    </div>
                    
                    <div id="selectedUserDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selected User</label>
                        <div id="selectedUserInfo" class="p-3 bg-blue-50 border border-blue-200 rounded-md"></div>
                        <input type="hidden" id="selectedUserId" name="user_id">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="developer">Developer</option>
                            <option value="designer">Designer</option>
                            <option value="tester">Tester</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button type="button" onclick="hideAddMemberModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let searchTimeout;

function showAddMemberModal() {
    document.getElementById('addMemberModal').classList.remove('hidden');
}

function hideAddMemberModal() {
    document.getElementById('addMemberModal').classList.add('hidden');
    document.getElementById('addMemberForm').reset();
    document.getElementById('selectedUserDiv').classList.add('hidden');
    document.getElementById('searchResults').classList.add('hidden');
}

// User search functionality
document.getElementById('userSearch').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value;
    
    if (query.length < 2) {
        document.getElementById('searchResults').classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        searchUsers(query);
    }, 300);
});

function searchUsers(query) {
    fetch(`{{ route('leader.search-users', $project->project_id) }}?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(users => {
        displaySearchResults(users);
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (users.length === 0) {
        resultsDiv.innerHTML = '<div class="p-3 text-sm text-gray-500">No users found</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    const html = users.map(user => `
        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" onclick="selectUser(${user.user_id}, '${user.full_name}', '${user.email}', '${user.role}')">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-xs font-medium text-gray-700">
                    ${user.full_name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">${user.full_name}</p>
                    <p class="text-xs text-gray-500">${user.email}</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        ${user.role}
                    </span>
                </div>
            </div>
        </div>
    `).join('');
    
    resultsDiv.innerHTML = html;
    resultsDiv.classList.remove('hidden');
}

function selectUser(userId, fullName, email, role) {
    document.getElementById('selectedUserId').value = userId;
    document.getElementById('selectedUserInfo').innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-300 rounded-full flex items-center justify-center text-xs font-medium text-blue-800">
                ${fullName.charAt(0).toUpperCase()}
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">${fullName}</p>
                <p class="text-xs text-gray-500">${email}</p>
            </div>
        </div>
    `;
    
    document.getElementById('selectedUserDiv').classList.remove('hidden');
    document.getElementById('searchResults').classList.add('hidden');
    document.getElementById('userSearch').value = fullName;
}

// Add member form submission
document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.user_id = document.getElementById('selectedUserId').value;
    
    if (!data.user_id) {
        alert('Please select a user');
        return;
    }
    
    fetch('{{ route("leader.add-member", $project->project_id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddMemberModal();
            showNotification('Member added successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding member', 'error');
    });
});

function updateMemberRole(userId, newRole) {
    fetch('{{ route("leader.update-role", $project->project_id) }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_id: userId,
            role: newRole
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Member role updated successfully!', 'success');
        } else {
            showNotification('Error: ' + data.message, 'error');
            location.reload(); // Reload to reset the select value
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating role', 'error');
        location.reload();
    });
}

function removeMember(userId, userName) {
    if (confirm(`Are you sure you want to remove ${userName} from the project?`)) {
        fetch('{{ route("leader.remove-member", $project->project_id) }}', {
            method: 'DELETE',
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
            if (data.success) {
                showNotification('Member removed successfully!', 'success');
                // Remove the member item from the DOM
                document.querySelector(`[data-user-id="${userId}"]`).remove();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while removing member', 'error');
        });
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
@endsection