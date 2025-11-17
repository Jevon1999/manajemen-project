@extends('layout.app')

@section('title', 'Export Reports')
@section('page-title', 'Export Reports')
@section('page-description', 'Generate dan export laporan sistem ke Excel')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    {{-- Header Info --}}
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 mb-8 text-white" data-aos="fade-down">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-file-excel mr-2"></i>Excel Export Center
        </h1>
        <p class="text-blue-100">Download laporan terstruktur dalam format Excel dengan styling professional</p>
    </div>

    {{-- Export Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        {{-- Projects Report --}}
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-all duration-300" data-aos="fade-up">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="ml-4 text-lg font-bold text-gray-900">Projects Report</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Export data projects lengkap dengan detail status, priority, dan tim</p>
            <div class="space-y-2">
                <a href="{{ route('admin.reports.export.projects') }}" 
                   class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-download mr-2"></i>Export All Projects
                </a>
                <a href="{{ route('admin.reports.export.projects', ['status' => 'active']) }}" 
                   class="block w-full bg-green-500 hover:bg-green-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-play-circle mr-2"></i>Active Projects Only
                </a>
                <a href="{{ route('admin.reports.export.projects', ['status' => 'completed']) }}" 
                   class="block w-full bg-purple-500 hover:bg-purple-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-check-circle mr-2"></i>Completed Projects
                </a>
            </div>
        </div>

        {{-- Tasks Report --}}
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="ml-4 text-lg font-bold text-gray-900">Tasks Report</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Export data tasks dengan info assignment, deadline, dan status</p>
            <div class="space-y-2">
                <a href="{{ route('admin.reports.export.tasks') }}" 
                   class="block w-full bg-green-500 hover:bg-green-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-download mr-2"></i>Export All Tasks
                </a>
                <a href="{{ route('admin.reports.export.tasks', ['status' => 'todo']) }}" 
                   class="block w-full bg-red-500 hover:bg-red-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-tasks mr-2"></i>To Do Tasks
                </a>
                <a href="{{ route('admin.reports.export.tasks', ['status' => 'done']) }}" 
                   class="block w-full bg-purple-500 hover:bg-purple-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-check-double mr-2"></i>Completed Tasks
                </a>
            </div>
        </div>

        {{-- Users Report --}}
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
            <div class="flex items-center mb-4">
                <div class="bg-cyan-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="ml-4 text-lg font-bold text-gray-900">Users Report</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Export data users lengkap dengan role dan status aktif</p>
            <div class="space-y-2">
                <a href="{{ route('admin.reports.export.users') }}" 
                   class="block w-full bg-cyan-500 hover:bg-cyan-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-download mr-2"></i>Export All Users
                </a>
                <a href="{{ route('admin.reports.export.users', ['role' => 'leader']) }}" 
                   class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-user-tie mr-2"></i>Leaders Only
                </a>
                <a href="{{ route('admin.reports.export.users', ['role' => 'user']) }}" 
                   class="block w-full bg-gray-500 hover:bg-gray-600 text-white text-center py-2.5 px-4 rounded-lg transition-all font-medium">
                    <i class="fas fa-users mr-2"></i>Members Only
                </a>
            </div>
        </div>

    </div>

    {{-- Comprehensive Report --}}
    <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 rounded-lg shadow-xl p-8 text-white" data-aos="fade-up" data-aos-delay="300">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex-1">
                <h3 class="text-3xl font-bold mb-3">
                    <i class="fas fa-file-excel mr-3"></i>Comprehensive Report
                </h3>
                <p class="text-purple-100 mb-4 text-lg">Export SEMUA data sistem dalam satu file Excel dengan multiple sheets terpisah</p>
                <ul class="space-y-2 text-purple-100">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-300"></i>
                        <span><strong>Sheet 1:</strong> All Projects Data (14 columns)</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-300"></i>
                        <span><strong>Sheet 2:</strong> All Tasks Data (11 columns)</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-300"></i>
                        <span><strong>Sheet 3:</strong> All Users Data (7 columns)</span>
                    </li>
                </ul>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.reports.export.comprehensive') }}" 
                   class="inline-block bg-white text-purple-600 hover:bg-purple-50 font-bold py-4 px-10 rounded-xl transition-all shadow-2xl hover:shadow-3xl hover:scale-105 transform">
                    <i class="fas fa-download mr-3 text-xl"></i>
                    <span class="text-xl">Download Full Report</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Export Features --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg" data-aos="fade-right" data-aos-delay="400">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Export Features</h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Format Excel (.xlsx) dengan styling professional</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Header berwarna (Blue/Green/Cyan) dengan bold text</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Auto column width untuk readability</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Formatted dates (d M Y) dan currency (Rp)</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Filter by status untuk reports spesifik</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- File Information --}}
        <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg" data-aos="fade-left" data-aos-delay="500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">File Information</h3>
                    <ul class="space-y-2 text-sm text-green-800">
                        <li><i class="fas fa-file-excel text-green-600 mr-2"></i><strong>Format:</strong> Microsoft Excel (.xlsx)</li>
                        <li><i class="fas fa-clock text-blue-600 mr-2"></i><strong>Filename:</strong> report_YYYY-MM-DD_HisHis.xlsx</li>
                        <li><i class="fas fa-database text-purple-600 mr-2"></i><strong>Data:</strong> Realtime dari database</li>
                        <li><i class="fas fa-download text-orange-600 mr-2"></i><strong>Download:</strong> Instant browser download</li>
                        <li><i class="fas fa-layer-group text-indigo-600 mr-2"></i><strong>Sheets:</strong> Single atau multiple sheets</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
