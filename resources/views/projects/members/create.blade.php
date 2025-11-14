@extends('layout.app')

@section('title', 'Tambah Anggota - ' . $project->project_name)
@section('page-title', 'Tambah Anggota Project')
@section('page-description', $project->project_name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('projects.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Projects
                </a>
            </li>
            <li class="inline-flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="{{ route('projects.members.index', $project->project_id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    {{ $project->project_name }}
                </a>
            </li>
            <li class="inline-flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-500 text-sm font-medium">Tambah Anggota</span>
            </li>
        </ol>
    </nav>

    <!-- Project Info Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Tambah Anggota Baru</h1>
                    <p class="text-gray-600">Tambahkan anggota ke project: <strong>{{ $project->project_name }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('projects.members.store', $project->project_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- User Search Section -->
            <div>
                <label for="user_search" class="block text-sm font-medium text-gray-700 mb-2">
                    Cari User untuk Ditambahkan <span class="text-red-500">*</span>
                </label>
                
                <!-- Information Alert -->
                <div class="mb-3 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi Penting</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Hanya user dengan role <strong>User</strong> yang dapat ditambahkan sebagai anggota project</li>
                                    <li><strong>Admin</strong> dan <strong>Leader</strong> tidak akan ditampilkan dalam hasil pencarian</li>
                                    <li>Satu project hanya memiliki satu leader yang sudah ditentukan saat pembuatan project</li>
                                    <li>User yang sudah menjadi anggota tidak akan ditampilkan dalam hasil pencarian</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <input type="text" 
                           id="user_search" 
                           placeholder="Cari berdasarkan nama, username, atau email..."
                           autocomplete="off"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 pr-10">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Search Results Dropdown -->
                    <div id="user_dropdown" class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                        <div id="user_results"></div>
                    </div>
                </div>
                
                <!-- Selected User Display -->
                <div id="selected_user" class="hidden mt-3 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium" id="user_initial"></span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" id="user_name"></p>
                                <p class="text-sm text-gray-600" id="user_info"></p>
                                <span id="user_role_badge" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1"></span>
                            </div>
                        </div>
                        <button type="button" 
                                id="remove_user" 
                                class="text-red-500 hover:text-red-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Hidden input for selected user -->
                <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}">
                
                @error('user_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    Role dalam Project <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Developer -->
                    <div class="relative">
                        <input type="radio" 
                               id="role_developer" 
                               name="role" 
                               value="developer"
                               {{ old('role') == 'developer' ? 'checked' : '' }}
                               class="sr-only peer">
                        <label for="role_developer" 
                               class="flex flex-col items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50">
                            <div class="text-2xl mb-2">💻</div>
                            <div class="text-center">
                                <div class="font-medium text-gray-900">Developer</div>
                                <div class="text-sm text-gray-600 mt-1">Mengembangkan fitur dan fungsionalitas</div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Designer -->
                    <div class="relative">
                        <input type="radio" 
                               id="role_designer" 
                               name="role" 
                               value="designer"
                               {{ old('role') == 'designer' ? 'checked' : '' }}
                               class="sr-only peer">
                        <label for="role_designer" 
                               class="flex flex-col items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                            <div class="text-2xl mb-2">🎨</div>
                            <div class="text-center">
                                <div class="font-medium text-gray-900">Designer</div>
                                <div class="text-sm text-gray-600 mt-1">Mendesain UI/UX dan visual</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('projects.members.index', $project->project_id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                    </svg>
                    Kembali
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Anggota
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript untuk User Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
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
        fetch(`{{ route('projects.members.search', $project->project_id) }}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
            results.innerHTML = '<div class="p-3 text-red-600">Terjadi kesalahan saat mencari user</div>';
            dropdown.classList.remove('hidden');
        });
    }

    // Display search results
    function displayResults(users) {
        if (users.length === 0) {
            results.innerHTML = '<div class="p-3 text-gray-500">Tidak ada user ditemukan atau semua user sudah menjadi anggota</div>';
        } else {
            results.innerHTML = users.map(user => {
                // Hanya user dengan role 'user' yang akan ditampilkan
                const roleColor = 'bg-blue-100 text-blue-800';
                const roleIcon = '👤';
                const roleText = 'User';
                
                return `
                    <div class="user-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                         data-user-id="${user.user_id}" 
                         data-user-name="${user.full_name}"
                         data-user-username="${user.username}"
                         data-user-email="${user.email}"
                         data-user-role="${user.role}">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">${user.full_name.charAt(0)}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">${user.full_name}</p>
                                <p class="text-sm text-gray-600">@${user.username} • ${user.email}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${roleColor}">
                                ${roleIcon} ${roleText}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
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
        const userRole = userItem.dataset.userRole;
        
        selectUser(userId, userName, userUsername, userEmail, userRole);
    });

    // Select user
    function selectUser(id, name, username, email, role) {
        userIdInput.value = id;
        searchInput.value = '';
        dropdown.classList.add('hidden');
        
        // Show selected user
        document.getElementById('user_initial').textContent = name.charAt(0);
        document.getElementById('user_name').textContent = name;
        document.getElementById('user_info').textContent = `@${username} • ${email}`;
        
        const roleBadge = document.getElementById('user_role_badge');
        // Hanya user dengan role 'user' yang bisa dipilih
        roleBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1';
        roleBadge.textContent = '👤 User';
        
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

    // Restore selected user if there's old value
    @if(old('user_id'))
        userIdInput.value = '{{ old('user_id') }}';
    @endif
});
</script>
@endsection