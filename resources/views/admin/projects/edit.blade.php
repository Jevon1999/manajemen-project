@extends('layout.app')

@section('title', 'Edit Proyek')
@section('page-title', 'Edit Proyek')
@section('page-description', 'Perbarui informasi proyek')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Proyek
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
            <div class="flex items-center space-x-3">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-2.5 rounded-lg">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Edit Proyek</h2>
                    <p class="text-green-100 text-sm">{{ $project->project_name }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.projects.update', $project->project_id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-6">
                    <button type="button" onclick="switchTab('basic')" id="tab-basic"
                            class="tab-button active whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Info Dasar</span>
                        </div>
                    </button>
                    <button type="button" onclick="switchTab('settings')" id="tab-settings"
                            class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-all">
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

            <!-- Tab Content: Basic Info -->
            <div id="content-basic" class="tab-content space-y-6">
                <!-- Project Name -->
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Proyek <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="project_name" name="project_name" value="{{ old('project_name', $project->project_name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                           placeholder="Masukkan nama proyek">
                    @error('project_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description/Requirements -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Requirements & Deskripsi Project <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                              placeholder="Jelaskan requirements dan deskripsi detail tentang project ini...">{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row: Status & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            <option value="planning" {{ old('status', $project->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>Ditunda</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioritas
                        </label>
                        <select id="priority" name="priority"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            <option value="low" {{ old('priority', $project->priority) == 'low' ? 'selected' : '' }}>Rendah</option>
                            <option value="medium" {{ old('priority', $project->priority) == 'medium' ? 'selected' : '' }}>Sedang</option>
                            <option value="high" {{ old('priority', $project->priority) == 'high' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                    </div>
                </div>

                <!-- Deadline & Completion -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Deadline -->
                    <div>
                        <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">
                            Deadline
                        </label>
                        <input type="date" id="deadline" name="deadline" value="{{ old('deadline', $project->deadline) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Completion Percentage -->
                    <div>
                        <label for="completion_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                            Progress (%)
                        </label>
                        <input type="number" id="completion_percentage" name="completion_percentage" 
                               value="{{ old('completion_percentage', $project->completion_percentage ?? 0) }}" 
                               min="0" max="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Tab Content: Settings -->
            <div id="content-settings" class="tab-content hidden space-y-6">
                <!-- Checkboxes -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="public_visibility" name="public_visibility" value="1"
                               {{ old('public_visibility', $project->public_visibility) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="public_visibility" class="ml-3 text-sm text-gray-700">
                            Visibilitas Publik
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="allow_member_invite" name="allow_member_invite" value="1"
                               {{ old('allow_member_invite', $project->allow_member_invite ?? true) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="allow_member_invite" class="ml-3 text-sm text-gray-700">
                            Izinkan Undangan Member
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1"
                               {{ old('notifications_enabled', $project->notifications_enabled ?? true) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="notifications_enabled" class="ml-3 text-sm text-gray-700">
                            Aktifkan Notifikasi
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_archived" name="is_archived" value="1"
                               {{ old('is_archived', $project->is_archived) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_archived" class="ml-3 text-sm text-gray-700">
                            Arsipkan Proyek
                        </label>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Informasi Proyek</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><span class="font-medium">Dibuat:</span> {{ $project->created_at ? $project->created_at->format('d M Y H:i') : '-' }}</p>
                        <p><span class="font-medium">Terakhir Update:</span> {{ $project->updated_at ? $project->updated_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <button type="button" onclick="window.history.back()" 
                   class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all shadow-md hover:shadow-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<style>
.tab-button {
    border-color: transparent;
    color: #6b7280;
}

.tab-button.active {
    border-color: #10b981;
    color: #10b981;
}

.tab-button:hover:not(.active) {
    border-color: #d1d5db;
    color: #374151;
}
</style>

<script>
function switchTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Add active class to selected button
    document.getElementById('tab-' + tab).classList.add('active');
}
</script>
@endpush
@endsection
