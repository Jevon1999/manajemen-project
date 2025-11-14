@extends('layout.app')

@section('title', 'Anggota Project - ' . $project->project_name)
@section('page-title', 'Anggota Project')
@section('page-description', $project->project_name)

@section('content')
<div class="max-w-7xl mx-auto">
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
                <span class="text-gray-500 text-sm font-medium">{{ $project->project_name }}</span>
            </li>
            <li class="inline-flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-500 text-sm font-medium">Anggota</span>
            </li>
        </ol>
    </nav>

    <!-- Project Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $project->project_name }}</h1>
                        <p class="text-gray-600">{{ $members->count() }} anggota dalam project ini</p>
                    </div>
                </div>
                
                @auth
                    @if(auth()->user()->user_id === $project->leader_id && auth()->user()->role !== 'admin')
                        <a href="{{ route('projects.members.create', $project->project_id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Anggota
                        </a>
                    @else
                        <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            @if(auth()->user()->role === 'admin')
                                Admin tidak dapat mengelola anggota project
                            @else
                                Hanya Leader yang dapat mengelola anggota
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Members List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="space-y-6">
                @forelse($members as $member)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar -->
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-lg">
                                {{ substr($member->user->full_name, 0, 1) }}
                            </span>
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $member->user->full_name }}</h3>
                                
                                <!-- User Role Badge -->
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    @if($member->user->role === 'admin') bg-red-100 text-red-800
                                    @elseif($member->user->role === 'leader') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    @if($member->user->role === 'admin') 👑 Admin
                                    @elseif($member->user->role === 'leader') 👨‍💼 Leader  
                                    @else 👤 User @endif
                                </span>
                                
                                <!-- Project Role Badge -->
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($member->role === 'project_manager') bg-purple-100 text-purple-800
                                    @elseif($member->role === 'developer') bg-green-100 text-green-800
                                    @else bg-orange-100 text-orange-800 @endif">
                                    @if($member->role === 'project_manager') 🚀 Project Manager
                                    @elseif($member->role === 'developer') 💻 Developer
                                    @else 🎨 Designer @endif
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                    {{ $member->user->email }}
                                </span>
                                
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"/>
                                    </svg>
                                    Bergabung {{ $member->joined_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons (Hanya untuk Leader dan bukan Admin) -->
                    @auth
                        @if(auth()->user()->user_id === $project->leader_id && auth()->user()->role !== 'admin')
                            <div class="flex items-center space-x-2">
                                <!-- Change Role Dropdown (Hanya developer dan designer) -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Ubah Role
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         @click.away="open = false"
                                         x-transition
                                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                        <div class="py-1">
                                            <button onclick="updateMemberRole({{ $member->member_id }}, 'developer')"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                � Developer
                                            </button>
                                            <button onclick="updateMemberRole({{ $member->member_id }}, 'designer')"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                🎨 Designer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Remove Member Button -->
                                <button onclick="removeMember({{ $member->member_id }}, '{{ $member->user->full_name }}')"
                                        class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center">
                                @if(auth()->user()->role === 'admin')
                                    <span class="text-sm text-gray-500 italic">Admin tidak dapat mengelola anggota project</span>
                                @else
                                    <span class="text-sm text-gray-500 italic">Hanya leader yang dapat mengelola anggota</span>
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>
                @empty
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Anggota</h3>
                    <p class="text-gray-600 mb-4">Project ini belum memiliki anggota tim.</p>
                    @auth
                        @if(auth()->user()->user_id === $project->leader_id && auth()->user()->role !== 'admin')
                            <a href="{{ route('projects.members.create', $project->project_id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Tambah Anggota Pertama
                            </a>
                        @else
                            <div class="text-sm text-gray-500 italic">
                                @if(auth()->user()->role === 'admin')
                                    Admin tidak dapat menambahkan anggota project
                                @else
                                    Hanya team leader yang dapat menambahkan anggota
                                @endif
                            </div>
                        @endif
                    @endauth
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js for dropdowns -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Update member role
function updateMemberRole(memberId, newRole) {
    if (!confirm('Apakah Anda yakin ingin mengubah role anggota ini?')) {
        return;
    }
    
    fetch(`{{ route('projects.members.update', ['project' => $project->project_id, 'member' => '__MEMBER_ID__']) }}`.replace('__MEMBER_ID__', memberId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ role: newRole })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengubah role');
    });
}

// Remove member
function removeMember(memberId, memberName) {
    if (!confirm(`Apakah Anda yakin ingin mengeluarkan ${memberName} dari project ini?`)) {
        return;
    }
    
    fetch(`{{ route('projects.members.destroy', ['project' => $project->project_id, 'member' => '__MEMBER_ID__']) }}`.replace('__MEMBER_ID__', memberId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengeluarkan anggota');
    });
}
</script>
@endsection