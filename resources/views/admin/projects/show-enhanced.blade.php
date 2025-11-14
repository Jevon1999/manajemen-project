@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex-1 p-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detail Proyek</h1>
                <p class="mt-1 text-sm text-gray-500">Informasi dan manajemen proyek</p>
            </div>
            <div class="flex space-x-3">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Sunting
                </button>
                <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Tugas
                </button>
            </div>
        </div>

        <!-- Project Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Project Info -->
            <div class="col-span-2 bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-medium text-gray-900 mb-4">Informasi Proyek</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Proyek</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ketua Proyek</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->leader_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Mulai</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->start_date ? date('d M Y', strtotime($project->start_date)) : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tenggat Waktu</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->due_date ? date('d M Y', strtotime($project->due_date)) : '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->description ?? 'Tidak ada deskripsi' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Project Stats -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-medium text-gray-900 mb-4">Statistik</h2>
                    <dl class="grid grid-cols-1 gap-6">
                        <div class="bg-gray-50 px-4 py-5 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Progress Keseluruhan</dt>
                            <dd class="mt-2">
                                <div class="flex items-center">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">{{ $project->progress ?? 0 }}%</span>
                                </div>
                            </dd>
                        </div>

                        <div class="bg-gray-50 px-4 py-5 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Status Tugas</dt>
                            <dd class="mt-2">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Total Tugas</span>
                                        <span class="font-medium text-gray-900">{{ $project->total_tasks ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Selesai</span>
                                        <span class="font-medium text-green-600">{{ $project->completed_tasks ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Dalam Proses</span>
                                        <span class="font-medium text-blue-600">{{ $project->in_progress_tasks ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Menunggu</span>
                                        <span class="font-medium text-yellow-600">{{ $project->pending_tasks ?? 0 }}</span>
                                    </div>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Team Members & Tasks -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Team Members -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-medium text-gray-900">Anggota Tim</h2>
                        <button class="text-sm text-blue-600 hover:text-blue-700">
                            Tambah Anggota
                        </button>
                    </div>
                    <div class="space-y-4">
                        @forelse($project->members ?? [] as $member)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($member->role) }}</p>
                                </div>
                                <div>
                                    <button class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada anggota tim</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Tasks -->
            <div class="col-span-2 bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-medium text-gray-900">Daftar Tugas</h2>
                        <div class="flex space-x-2">
                            <select class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option>Semua Status</option>
                                <option>Belum Dimulai</option>
                                <option>Dalam Proses</option>
                                <option>Selesai</option>
                            </select>
                            <button class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="-ml-1 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Tambah
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($project->tasks ?? [] as $task)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                            <p class="text-sm text-gray-500">{{ Str::limit($task->description, 100) }}</p>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $task->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                                                    {{ ucfirst($task->status) }}
                                                </span>
                                                @if($task->due_date)
                                                    <span class="text-xs text-gray-500">
                                                        Tenggat: {{ date('d M Y', strtotime($task->due_date)) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button class="text-gray-400 hover:text-gray-500">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button class="text-gray-400 hover:text-gray-500">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada tugas</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan tugas baru untuk proyek ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection