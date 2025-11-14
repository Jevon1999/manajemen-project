@extends('layout.app')

@section('title', 'Buat Project Baru')

@section('page-title', 'Buat Project Baru')
@section('page-description', 'Buat project baru dan pilih team leader sebagai project manager')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Project Creation Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form id="createProjectForm" action="{{ route('admin.projects.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
                
                <!-- Project Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Project Name -->
                    <div>
                        <label for="project_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Project <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="project_name" 
                               name="project_name" 
                               required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan nama project"
                               value="{{ old('project_name') }}">
                        @error('project_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi Project
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Deskripsi detail tentang project ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('start_date') }}">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Selesai
                        </label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('end_date') }}">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Team Leader Selection -->
                <div>
                    <label for="leader_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Team Leader sebagai Project Manager
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="leader_search" 
                               placeholder="Cari team leader berdasarkan nama, username, atau email..."
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 pr-10">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        
                        <!-- Search Results Dropdown -->
                        <div id="leader_dropdown" class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            <div id="leader_results"></div>
                        </div>
                    </div>
                    
                    <!-- Selected Leader Display -->
                    <div id="selected_leader" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium" id="leader_initial"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900" id="leader_name"></p>
                                    <p class="text-sm text-gray-600" id="leader_info"></p>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                        👨‍💼 Project Manager
                                    </span>
                                </div>
                            </div>
                            <button type="button" 
                                    id="remove_leader" 
                                    class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Hidden input for selected leader -->
                    <input type="hidden" id="leader_id" name="leader_id" value="{{ old('leader_id') }}">
                    
                    @error('leader_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('projects.index') }}" 
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
                        Buat Project
                    </button>
                </div>
            </form>
        </div>
</div>

<!-- JavaScript untuk Leader Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('leader_search');
    const dropdown = document.getElementById('leader_dropdown');
    const results = document.getElementById('leader_results');
    const selectedLeaderDiv = document.getElementById('selected_leader');
    const leaderIdInput = document.getElementById('leader_id');
    const removeLeaderBtn = document.getElementById('remove_leader');
    
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
            searchLeaders(query);
        }, 300);
    });

    // Search leaders via API
    function searchLeaders(query) {
        fetch(`{{ route('leaders.search') }}?q=${encodeURIComponent(query)}`, {
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
            results.innerHTML = '<div class="p-3 text-red-600">Terjadi kesalahan saat mencari leader</div>';
            dropdown.classList.remove('hidden');
        });
    }

    // Display search results
    function displayResults(leaders) {
        if (leaders.length === 0) {
            results.innerHTML = '<div class="p-3 text-gray-500">Tidak ada leader ditemukan</div>';
        } else {
            results.innerHTML = leaders.map(leader => `
                <div class="leader-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-leader-id="${leader.user_id}" 
                     data-leader-name="${leader.full_name}"
                     data-leader-username="${leader.username}"
                     data-leader-email="${leader.email}">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">${leader.full_name.charAt(0)}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">${leader.full_name}</p>
                            <p class="text-sm text-gray-600">@${leader.username} • ${leader.email}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        dropdown.classList.remove('hidden');
    }

    // Handle leader selection
    results.addEventListener('click', function(e) {
        const leaderItem = e.target.closest('.leader-item');
        if (!leaderItem) return;
        
        const leaderId = leaderItem.dataset.leaderId;
        const leaderName = leaderItem.dataset.leaderName;
        const leaderUsername = leaderItem.dataset.leaderUsername;
        const leaderEmail = leaderItem.dataset.leaderEmail;
        
        selectLeader(leaderId, leaderName, leaderUsername, leaderEmail);
    });

    // Select leader
    function selectLeader(id, name, username, email) {
        leaderIdInput.value = id;
        searchInput.value = '';
        dropdown.classList.add('hidden');
        
        // Show selected leader
        document.getElementById('leader_initial').textContent = name.charAt(0);
        document.getElementById('leader_name').textContent = name;
        document.getElementById('leader_info').textContent = `@${username} • ${email}`;
        selectedLeaderDiv.classList.remove('hidden');
    }

    // Remove selected leader
    removeLeaderBtn.addEventListener('click', function() {
        leaderIdInput.value = '';
        selectedLeaderDiv.classList.add('hidden');
        searchInput.value = '';
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Restore selected leader if there's old value
    @if(old('leader_id'))
        // This would need to fetch leader data from server if needed
        // For now, just set the hidden input value
        leaderIdInput.value = '{{ old('leader_id') }}';
    @endif
});
</script>
@endsection