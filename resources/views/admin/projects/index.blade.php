@extends('layout.app')

@section('title', 'Manajemen Proyek')
@section('page-title', 'Manajemen Proyek')
@section('page-description', 'Kelola dan monitor semua proyek dalam sistem')

@section('content')
<div id="projectApp" class="space-y-6">
    <!-- Container Statistik -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-aos="fade-up">
        <!-- Total Proyek -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Proyek</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $totalProjects ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proyek Aktif -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Proyek Aktif</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $activeProjects ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anggota Aktif -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-purple-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Anggota Aktif</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $activeMembers ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prioritas Tinggi -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-red-500 p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Prioritas Tinggi</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $highPriorityProjects ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg" data-aos="fade-up">
        <!-- Header & Button -->
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Daftar Proyek
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Kelola semua proyek yang ada</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('admin.projects.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Proyek Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="px-4 py-4 bg-gray-50 border-b border-gray-200 sm:px-6">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Search -->
                <div class="lg:col-span-1">
                    <label for="search" class="sr-only">Cari</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Cari proyek...">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Semua Status</option>
                        <option value="planning">Planning</option>
                        <option value="active">Aktif</option>
                        <option value="on_hold">Ditunda</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="sr-only">Kategori</label>
                    <select id="category" name="category" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Semua Kategori</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label for="priority" class="sr-only">Prioritas</label>
                    <select id="priority" name="priority" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Semua Prioritas</option>
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Project Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @forelse($projects ?? [] as $project)
            @php
                $statusConfig = [
                    'planning' => ['class' => 'bg-gray-100 text-gray-800 border-gray-300', 'label' => 'Planning', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'active' => ['class' => 'bg-green-100 text-green-800 border-green-300', 'label' => 'Aktif', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    'on_hold' => ['class' => 'bg-yellow-100 text-yellow-800 border-yellow-300', 'label' => 'Ditunda', 'icon' => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'completed' => ['class' => 'bg-blue-100 text-blue-800 border-blue-300', 'label' => 'Selesai', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'cancelled' => ['class' => 'bg-red-100 text-red-800 border-red-300', 'label' => 'Dibatalkan', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z']
                ];
                $status = $project->status ?? 'planning';
                $config = $statusConfig[$status] ?? $statusConfig['planning'];
                
                $priorityConfig = [
                    'low' => ['class' => 'bg-green-50 text-green-700 border-green-200', 'label' => 'Rendah'],
                    'medium' => ['class' => 'bg-yellow-50 text-yellow-700 border-yellow-200', 'label' => 'Sedang'],
                    'high' => ['class' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Tinggi']
                ];
                $priority = $project->priority ?? 'medium';
                $priorityStyle = $priorityConfig[$priority] ?? $priorityConfig['medium'];
            @endphp
            
            <!-- Project Card -->
            <div class="bg-white rounded-xl shadow hover:shadow-2xl transition-all duration-500 ease-in-out hover:-translate-y-3 hover:scale-[1.02] overflow-hidden border border-gray-100 group ml-3 mr-1 my-1">
                <!-- Card Header with Gradient -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-4 py-3 relative overflow-hidden transition-all duration-500">
                    
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0 h-10 w-10 bg-white rounded-lg shadow flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-sm">
                                    {{ strtoupper(substr($project->project_name ?? $project->name ?? 'P', 0, 2)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-semibold text-sm truncate">
                                    {{ $project->project_name ?? $project->name ?? 'Unnamed Project' }}
                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $priorityStyle['class'] }} border mt-0.5">
                                    {{ $priorityStyle['label'] }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons in Header -->
                        <div class="flex items-center space-x-2 ml-3">
                            <a href="{{ route('admin.projects.edit', $project->project_id ?? $project->id) }}" 
                               class="group/edit p-2 text-white hover:bg-white hover:bg-opacity-30 rounded-lg transition-all duration-300 hover:scale-110 backdrop-blur-sm active:scale-95" 
                               title="Edit Proyek">
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover/edit:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button type="button"
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus proyek ini?')) { deleteProject({{ $project->project_id ?? $project->id }}) }" 
                                    class="group/delete p-2 text-white hover:bg-red-500 hover:bg-opacity-90 rounded-lg transition-all duration-300 hover:scale-110 backdrop-blur-sm active:scale-95" 
                                    title="Hapus Proyek">
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover/delete:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-4">
                    <!-- Description -->
                    <p class="text-xs text-gray-600 mb-3 line-clamp-2" style="min-height: 32px;">
                        {{ $project->description ?? 'Tidak ada deskripsi' }}
                    </p>

                    <!-- Leader Info -->
                    <div class="flex items-center space-x-2 mb-3 pb-3 border-b border-gray-100">
                        <div class="flex-shrink-0">
                            @if($project->leader && $project->leader->avatar)
                                <img class="h-7 w-7 rounded-full ring-2 ring-gray-200" src="{{ $project->leader->avatar }}" alt="">
                            @else
                                <div class="h-7 w-7 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center ring-2 ring-gray-200">
                                    <span class="text-xs font-semibold text-gray-600">
                                        {{ $project->leader ? strtoupper(substr($project->leader->full_name ?? $project->leader->username, 0, 2)) : 'NA' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500">Ketua Tim</p>
                            <p class="text-xs font-medium text-gray-900 truncate">
                                {{ $project->leader ? ($project->leader->full_name ?? $project->leader->username) : 'Belum ditentukan' }}
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-medium text-gray-600">Progress</span>
                            <span class="text-xs font-bold text-blue-600">{{ $project->completion_percentage ?? $project->progress ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $project->completion_percentage ?? $project->progress ?? 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Status & Deadline -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $config['class'] }} border">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                            </svg>
                            {{ $config['label'] }}
                        </span>
                        
                        @if(isset($project->deadline) || isset($project->end_date))
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($project->deadline ?? $project->end_date)->format('d M Y') }}
                        </div>
                        @endif
                    </div>

                    <!-- View Detail Button -->
                    <div class="pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.projects.show', $project->project_id ?? $project->id) }}" 
                           class="flex items-center justify-center w-full px-4 py-2 text-xs font-medium text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 rounded-lg transition-all">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat Detail Proyek
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-300 p-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="bg-gray-100 rounded-full p-6 mb-4">
                            <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada proyek</h3>
                        <p class="text-gray-500 mb-6 max-w-sm">Mulai dengan membuat proyek baru untuk mengelola tim dan tugas Anda</p>
                        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Buat Proyek Baru
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(isset($projects) && method_exists($projects, 'links'))
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $projects->links() }}
        </div>
        @endif
    </div>

    <!-- Enhanced Container -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg" data-aos="fade-up">
        <div id="enhanced-container" class="px-4 py-5 sm:p-6">
            <div class="text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="mt-2 text-sm">Enhanced features container</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Project (Vue.js) -->
<transition name="modal">
    <div v-if="showCreateModal" v-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" @click="closeCreateModal"></div>
        
        <!-- Modal Content -->
        <div class="relative mx-auto p-5 border w-full max-w-2xl shadow-2xl rounded-lg bg-white transform transition-all z-10" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Buat Proyek Baru</h3>
                </div>
                <button @click="closeCreateModal" type="button" 
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                        title="Tutup (ESC)">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="createProject" class="mt-6">
                @csrf
                <div class="space-y-5">
                <!-- Nama Proyek -->
                <div class="transform transition-all">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Proyek <span class="text-red-500">*</span>
                    </label>
                    <input v-model="createForm.project_name" type="text" required 
                           class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm"
                           placeholder="Masukkan nama proyek">
                </div>

                <!-- Deskripsi -->
                <div class="transform transition-all">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea v-model="createForm.description" rows="3" 
                              class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm"
                              placeholder="Deskripsikan proyek Anda..."></textarea>
                </div>

                <!-- Team Leader - REQUIRED -->
                <div class="transform transition-all">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Team Leader <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select v-model="createForm.leader_id" required
                                class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm appearance-none">
                            <option value="">-- Pilih Team Leader --</option>
                            @foreach($leaders as $leader)
                                <option value="{{ $leader->user_id }}">
                                    {{ $leader->full_name ?? $leader->username }}
                                    @if($leader->email)
                                        ({{ $leader->email }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Leader akan mengelola tim dan task proyek</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <!-- Status -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="relative">
                            <select v-model="createForm.status" 
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm appearance-none">
                                <option value="planning">🎯 Planning</option>
                                <option value="active">✅ Aktif</option>
                                <option value="on_hold">⏸️ Ditunda</option>
                                <option value="completed">🎉 Selesai</option>
                                <option value="cancelled">❌ Dibatalkan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Prioritas -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prioritas</label>
                        <div class="relative">
                            <select v-model="createForm.priority" 
                                    class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm appearance-none">
                                <option value="low">🟢 Rendah</option>
                                <option value="medium">🟡 Sedang</option>
                                <option value="high">🔴 Tinggi</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <!-- Kategori -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                        <input v-model="createForm.category" type="text" 
                               class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm" 
                               placeholder="Web, Mobile, dll">
                    </div>

                    <!-- Deadline -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                        <input v-model="createForm.end_date" type="date" 
                               class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm">
                    </div>
                </div>

                <!-- Budget -->
                <div class="transform transition-all">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Budget (Opsional)</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium sm:text-sm">Rp</span>
                        </div>
                        <input v-model="createForm.budget" type="number" 
                               class="block w-full pl-12 pr-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm" 
                               placeholder="0">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button" @click="closeCreateModal" 
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                    Batal
                </button>
                <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="!loading">Buat Proyek</span>
                    <span v-else class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Membuat...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
</transition>

<!-- Modal Edit Project (Vue.js) -->
<transition name="modal">
    <div v-if="showEditModal" v-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" @click="closeEditModal"></div>
        
        <!-- Modal Content -->
        <div class="relative mx-auto border w-full max-w-3xl shadow-2xl rounded-lg bg-white transform transition-all z-10" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-2.5 rounded-lg shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Proyek</h3>
                        <p class="text-xs text-gray-600">Perbarui informasi proyek Anda</p>
                    </div>
                </div>
                <button @click="closeEditModal" type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-white hover:shadow-md"
                        title="Tutup (ESC)">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="updateProject" class="modal-content p-6">
                @csrf
                @method('PUT')
            
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-6">
                    <button @click="activeTab = 'basic'" :class="activeTab === 'basic' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Info Dasar</span>
                        </div>
                    </button>
                    <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Pengaturan</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="space-y-5">
                <!-- Tab: Info Dasar -->
                <div v-show="activeTab === 'basic'" class="space-y-5">
                    <!-- Nama Proyek -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Proyek <span class="text-red-500">*</span>
                        </label>
                        <input v-model="editForm.project_name" type="text" required 
                               class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm"
                               placeholder="Masukkan nama proyek">
                    </div>

                    <!-- Deskripsi -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                        <textarea v-model="editForm.description" rows="4" 
                                  class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm"
                                  placeholder="Deskripsikan proyek Anda..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">@{{ editForm.description ? editForm.description.length : 0 }} karakter</p>
                    </div>

                    <!-- Progress Bar dengan Slider -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Progress Penyelesaian
                            <span class="ml-2 text-green-600 font-bold">@{{ editForm.completion_percentage || 0 }}%</span>
                        </label>
                        <div class="space-y-3">
                            <input v-model="editForm.completion_percentage" type="range" min="0" max="100" 
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-green"
                                   @input="updateProgress">
                            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="h-full transition-all duration-500 rounded-full flex items-center justify-end pr-2"
                                     :style="{ width: (editForm.completion_percentage || 0) + '%', backgroundColor: getProgressColor(editForm.completion_percentage) }">
                                    <span v-if="editForm.completion_percentage > 10" class="text-xs font-bold text-white">
                                        @{{ editForm.completion_percentage }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Status -->
                        <div class="transform transition-all">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Proyek</label>
                            <div class="relative">
                                <select v-model="editForm.status" 
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm appearance-none">
                                    <option value="planning">🎯 Planning</option>
                                    <option value="active">✅ Aktif</option>
                                    <option value="on_hold">⏸️ Ditunda</option>
                                    <option value="completed">🎉 Selesai</option>
                                    <option value="cancelled">❌ Dibatalkan</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Prioritas -->
                        <div class="transform transition-all">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prioritas</label>
                            <div class="relative">
                                <select v-model="editForm.priority" 
                                        class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm appearance-none">
                                    <option value="low">🟢 Rendah</option>
                                    <option value="medium">🟡 Sedang</option>
                                    <option value="high">🔴 Tinggi</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Kategori -->
                        <div class="transform transition-all">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                            <input v-model="editForm.category" type="text" 
                                   class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm" 
                                   placeholder="Web, Mobile, Desktop, dll">
                        </div>

                        <!-- Deadline -->
                        <div class="transform transition-all">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                            <input v-model="editForm.deadline" type="date" 
                                   class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm">
                            <p v-if="editForm.deadline" class="mt-1 text-xs" :class="isDeadlineClose(editForm.deadline) ? 'text-red-600' : 'text-gray-500'">
                                <span v-if="isDeadlineClose(editForm.deadline)">⚠️ Deadline mendekat!</span>
                                <span v-else>📅 @{{ formatDeadline(editForm.deadline) }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Budget -->
                    <div class="transform transition-all">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Budget Proyek</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-medium sm:text-sm">Rp</span>
                            </div>
                            <input v-model="editForm.budget" type="number" 
                                   class="block w-full pl-12 pr-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all sm:text-sm" 
                                   placeholder="0"
                                   @input="formatBudget">
                        </div>
                        <p v-if="editForm.budget" class="mt-1 text-xs text-gray-500">
                            💰 @{{ formatCurrency(editForm.budget) }}
                        </p>
                    </div>
                </div>

                <!-- Tab: Pengaturan -->
                <div v-show="activeTab === 'settings'" class="space-y-5">
                    <!-- Visibility Settings -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <h4 class="text-sm font-bold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Pengaturan Visibilitas
                        </h4>
                        
                        <!-- Public Visibility -->
                        <label class="flex items-center justify-between p-3 bg-white rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Proyek Publik</p>
                                    <p class="text-xs text-gray-500">Semua orang dapat melihat proyek ini</p>
                                </div>
                            </div>
                            <input v-model="editForm.public_visibility" type="checkbox" 
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded transition-all">
                        </label>

                        <!-- Allow Member Invite -->
                        <label class="flex items-center justify-between p-3 bg-white rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Izinkan Undang Anggota</p>
                                    <p class="text-xs text-gray-500">Member dapat mengundang anggota baru</p>
                                </div>
                            </div>
                            <input v-model="editForm.allow_member_invite" type="checkbox" 
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded transition-all">
                        </label>

                        <!-- Notifications Enabled -->
                        <label class="flex items-center justify-between p-3 bg-white rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Notifikasi Aktif</p>
                                    <p class="text-xs text-gray-500">Terima notifikasi untuk proyek ini</p>
                                </div>
                            </div>
                            <input v-model="editForm.notifications_enabled" type="checkbox" 
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded transition-all">
                        </label>

                        <!-- Archived -->
                        <label class="flex items-center justify-between p-3 bg-white rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Arsipkan Proyek</p>
                                    <p class="text-xs text-gray-500">Proyek tidak akan muncul di daftar aktif</p>
                                </div>
                            </div>
                            <input v-model="editForm.is_archived" type="checkbox" 
                                   class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded transition-all">
                        </label>
                    </div>

                    <!-- Project Statistics (Read-only) -->
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Informasi Tambahan
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded p-3">
                                <p class="text-xs text-gray-500">Dibuat</p>
                                <p class="text-sm font-medium text-gray-900">@{{ editForm.created_at || '-' }}</p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="text-xs text-gray-500">Terakhir Update</p>
                                <p class="text-sm font-medium text-gray-900">@{{ editForm.updated_at || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-3 pt-5 border-t border-gray-200 bg-gray-50 -mx-6 -mb-6 px-6 py-4 rounded-b-lg">
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Terakhir diupdate: @{{ editForm.updated_at || '-' }}
                </div>
                <div class="flex space-x-3 w-full sm:w-auto">
                    <button type="button" @click="closeEditModal" 
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                        <span class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </span>
                    </button>
                    <button type="submit" :disabled="loading"
                            class="flex-1 sm:flex-none px-8 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 via-green-600 to-emerald-600 border border-transparent rounded-lg hover:from-green-700 hover:via-green-700 hover:to-emerald-700 hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <span v-if="!loading" class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</transition>

</div>
<!-- End Vue App -->

@push('scripts')
<style>
/* Prevent flashing of uncompiled Vue templates */
[v-cloak] {
    display: none !important;
}

/* Vue Modal Transitions */
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-active > div, .modal-leave-active > div {
    transition: transform 0.3s ease;
}
.modal-enter-from > div, .modal-leave-to > div {
    transform: scale(0.95) translateY(-20px);
}

/* Project Card Animations */
.group:hover {
    transform: translateY(-2px);
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Progress bar animation */
@keyframes progressAnimation {
    0% {
        width: 0;
    }
}

.bg-gradient-to-r {
    animation: progressAnimation 1s ease-out;
}

/* Custom Range Slider for Progress */
.slider-green::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.slider-green::-webkit-slider-thumb:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
}

.slider-green::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    cursor: pointer;
    border-radius: 50%;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.slider-green::-moz-range-thumb:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
}

/* Tab Animation */
.tab-content-enter-active, .tab-content-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.tab-content-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}
.tab-content-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

/* Checkbox Custom Style */
input[type="checkbox"]:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

/* Smooth scroll for modal content */
.modal-content {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #059669;
}

/* Pulse animation for notifications */
@keyframes pulse-green {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
}

.pulse-green {
    animation: pulse-green 2s infinite;
}
</style>

<script>
console.log('Vue available?', typeof Vue !== 'undefined');

if (typeof Vue !== 'undefined') {
    const { createApp } = Vue;

    const app = createApp({
        data() {
            return {
                showCreateModal: false,
                showEditModal: false,
            loading: false,
            activeTab: 'basic', // 'basic' or 'settings'
            createForm: {
                project_name: '',
                description: '',
                leader_id: '',
                status: 'planning',
                priority: 'medium',
                category: '',
                end_date: '',
                budget: ''
            },
            editForm: {
                project_id: null,
                project_name: '',
                description: '',
                status: 'planning',
                priority: 'medium',
                category: '',
                deadline: '',
                budget: '',
                completion_percentage: 0,
                public_visibility: false,
                allow_member_invite: true,
                notifications_enabled: true,
                is_archived: false,
                created_at: null,
                updated_at: null
            }
        }
    },
    methods: {
        openCreateModal() {
            this.showCreateModal = true;
            document.body.style.overflow = 'hidden';
        },
        closeCreateModal() {
            this.showCreateModal = false;
            document.body.style.overflow = 'auto';
            this.resetCreateForm();
        },
        openEditModal(projectId) {
            console.log('openEditModal called with projectId:', projectId);
            this.loading = true;
            this.activeTab = 'basic'; // Reset to basic tab
            console.log('Fetching project data from:', `/admin/projects/${projectId}/edit`);
            fetch(`/admin/projects/${projectId}/edit`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                const project = data.project;
                this.editForm = {
                    project_id: project.project_id,
                    project_name: project.project_name || '',
                    description: project.description || '',
                    status: project.status || 'planning',
                    priority: project.priority || 'medium',
                    category: project.category || '',
                    deadline: project.deadline || '',
                    budget: project.budget || '',
                    completion_percentage: project.completion_percentage || 0,
                    public_visibility: project.public_visibility || false,
                    allow_member_invite: project.allow_member_invite !== false,
                    notifications_enabled: project.notifications_enabled !== false,
                    is_archived: project.is_archived || false,
                    created_at: this.formatDateTime(project.created_at),
                    updated_at: this.formatDateTime(project.updated_at)
                };
                console.log('Edit form populated:', this.editForm);
                console.log('Setting showEditModal to true');
                this.showEditModal = true;
                document.body.style.overflow = 'hidden';
                this.loading = false;
                console.log('Modal should be visible now, showEditModal:', this.showEditModal);
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Gagal memuat data proyek', 'error');
                this.loading = false;
            });
        },
        closeEditModal() {
            this.showEditModal = false;
            document.body.style.overflow = 'auto';
            this.resetEditForm();
        },
        createProject() {
            this.loading = true;
            const formData = new FormData();
            Object.keys(this.createForm).forEach(key => {
                if (this.createForm[key]) {
                    formData.append(key, this.createForm[key]);
                }
            });

            fetch('/admin/projects', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.closeCreateModal();
                    this.showNotification(data.message || 'Proyek berhasil dibuat!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(data.message || 'Gagal membuat proyek', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                console.error('Error:', error);
                this.showNotification('Terjadi kesalahan saat membuat proyek', 'error');
            });
        },
        updateProject() {
            this.loading = true;
            const formData = new FormData();
            
            // Append all form fields except project_id, created_at, updated_at
            Object.keys(this.editForm).forEach(key => {
                if (key !== 'project_id' && key !== 'created_at' && key !== 'updated_at') {
                    const value = this.editForm[key];
                    // Convert boolean to 1/0 for Laravel
                    if (typeof value === 'boolean') {
                        formData.append(key, value ? '1' : '0');
                    } else if (value !== null && value !== '') {
                        formData.append(key, value);
                    }
                }
            });
            formData.append('_method', 'PUT');

            fetch(`/admin/projects/${this.editForm.project_id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.loading = false;
                if (data.message && data.message.includes('berhasil')) {
                    this.closeEditModal();
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(data.message || 'Gagal memperbarui proyek', 'error');
                }
            })
            .catch(error => {
                this.loading = false;
                console.error('Error:', error);
                this.showNotification('Terjadi kesalahan saat memperbarui proyek', 'error');
            });
        },
        deleteProject(projectId) {
            if (!confirm('Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }
            
            fetch(`/admin/projects/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message && data.message.includes('berhasil')) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('Gagal menghapus proyek: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Terjadi kesalahan saat menghapus proyek', 'error');
            });
        },
        resetCreateForm() {
            this.createForm = {
                project_name: '',
                description: '',
                status: 'planning',
                priority: 'medium',
                category: '',
                end_date: '',
                budget: ''
            };
        },
        resetEditForm() {
            this.editForm = {
                project_id: null,
                project_name: '',
                description: '',
                status: 'planning',
                priority: 'medium',
                category: '',
                deadline: '',
                budget: '',
                completion_percentage: 0,
                public_visibility: false,
                allow_member_invite: true,
                notifications_enabled: true,
                is_archived: false,
                created_at: null,
                updated_at: null
            };
            this.activeTab = 'basic';
        },
        updateProgress(event) {
            this.editForm.completion_percentage = parseInt(event.target.value);
        },
        getProgressColor(percentage) {
            if (percentage < 25) return '#ef4444'; // red
            if (percentage < 50) return '#f59e0b'; // orange
            if (percentage < 75) return '#eab308'; // yellow
            if (percentage < 100) return '#22c55e'; // green
            return '#10b981'; // emerald for 100%
        },
        formatDeadline(date) {
            if (!date) return '';
            const deadline = new Date(date);
            const now = new Date();
            const diffTime = deadline - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) return `Terlewat ${Math.abs(diffDays)} hari`;
            if (diffDays === 0) return 'Hari ini';
            if (diffDays === 1) return 'Besok';
            if (diffDays <= 7) return `${diffDays} hari lagi`;
            if (diffDays <= 30) return `${Math.ceil(diffDays / 7)} minggu lagi`;
            return `${Math.ceil(diffDays / 30)} bulan lagi`;
        },
        isDeadlineClose(date) {
            if (!date) return false;
            const deadline = new Date(date);
            const now = new Date();
            const diffDays = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
            return diffDays >= 0 && diffDays <= 7; // Warning if within 7 days
        },
        formatCurrency(amount) {
            if (!amount) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },
        formatBudget(event) {
            // Remove non-numeric characters
            let value = event.target.value.replace(/\D/g, '');
            this.editForm.budget = value;
        },
        formatDateTime(datetime) {
            if (!datetime) return '-';
            const date = new Date(datetime);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/>
                    </svg>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    },
    mounted() {
        console.log('Vue app mounted hook called');
        // Make methods available globally for onclick handlers
        window.openCreateModal = () => this.openCreateModal();
        window.openEditModal = (id) => {
            console.log('window.openEditModal called with id:', id);
            this.openEditModal(id);
        };
        window.deleteProject = (id) => this.deleteProject(id);
        
        console.log('Global functions registered:', {
            openCreateModal: typeof window.openCreateModal,
            openEditModal: typeof window.openEditModal,
            deleteProject: typeof window.deleteProject
        });
        
        // Ensure modals are closed on mount
        this.showEditModal = false;
        this.showCreateModal = false;
        console.log('Initial modal states:', {
            showEditModal: this.showEditModal,
            showCreateModal: this.showCreateModal
        });
        
        // ESC key handler for closing modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.showEditModal) {
                    this.closeEditModal();
                } else if (this.showCreateModal) {
                    this.closeCreateModal();
                }
            }
        });
    }
    });

    console.log('Mounting Vue app to #projectApp');
    app.mount('#projectApp');
    console.log('Vue app mounted successfully');
} else {
    console.error('Vue is not loaded! Check if Vue CDN is included in layout.');
}

// Live Filter - Keep this for filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status');
    const categoryFilter = document.getElementById('category');
    const priorityFilter = document.getElementById('priority');

    function applyFilters() {
        const params = new URLSearchParams(window.location.search);
        
        const search = searchInput.value;
        const status = statusFilter.value;
        const category = categoryFilter.value;
        const priority = priorityFilter.value;

        if (search) params.set('search', search); else params.delete('search');
        if (status) params.set('status', status); else params.delete('status');
        if (category) params.set('category', category); else params.delete('category');
        if (priority) params.set('priority', priority); else params.delete('priority');

        window.location.href = '{{ route("admin.projects.index") }}' + (params.toString() ? '?' + params.toString() : '');
    }

    // Search on Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    // Auto apply on filter change
    statusFilter.addEventListener('change', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    priorityFilter.addEventListener('change', applyFilters);

    // Restore filter values from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) searchInput.value = urlParams.get('search');
    if (urlParams.has('status')) statusFilter.value = urlParams.get('status');
    if (urlParams.has('category')) categoryFilter.value = urlParams.get('category');
    if (urlParams.has('priority')) priorityFilter.value = urlParams.get('priority');
});
</script>
@endpush

@endsection