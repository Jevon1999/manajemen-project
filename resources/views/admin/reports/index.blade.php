@extends('layout.app')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')
@section('page-description', 'Generate and export comprehensive project reports')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                @if($reportType == 'monthly')
                    📅 Monthly Reports
                @elseif($reportType == 'yearly')
                    📊 Yearly Reports
                @elseif($reportType == 'project')
                    📁 Project Reports
                @else
                    📈 Analytics Dashboard
                @endif
            </h1>
            <p class="text-gray-600">Comprehensive project performance reports and analytics</p>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 p-6 rounded-lg shadow-lg text-white">
            <div class="text-sm font-medium opacity-90">🏗️ Total Projects</div>
            <div class="text-3xl font-bold mt-2">{{ $statistics['total_projects'] ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 p-6 rounded-lg shadow-lg text-white">
            <div class="text-sm font-medium opacity-90">⚡ Active Projects</div>
            <div class="text-3xl font-bold mt-2">{{ $statistics['active_projects'] ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-700 p-6 rounded-lg shadow-lg text-white">
            <div class="text-sm font-medium opacity-90">📋 Total Tasks</div>
            <div class="text-3xl font-bold mt-2">{{ $statistics['total_tasks'] ?? 0 }}</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-700 p-6 rounded-lg shadow-lg text-white">
            <div class="text-sm font-medium opacity-90">✅ Completed</div>
            <div class="text-3xl font-bold mt-2">{{ $statistics['completed_tasks'] ?? 0 }}</div>
        </div>
    </div>

    @if(!$reportType || $reportType == 'general')
    <!-- Report Type Selection -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <a href="{{ route('admin.reports.index', ['type' => 'monthly']) }}" 
           class="bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-blue-500 hover:shadow-lg transition-all group">
            <div class="text-center">
                <div class="text-4xl mb-3">📅</div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 mb-2">Monthly Reports</h3>
                <p class="text-sm text-gray-600">Performance overview for the last 12 months</p>
            </div>
        </a>
        
        <a href="{{ route('admin.reports.index', ['type' => 'yearly']) }}" 
           class="bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-purple-500 hover:shadow-lg transition-all group">
            <div class="text-center">
                <div class="text-4xl mb-3">📊</div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-purple-600 mb-2">Yearly Reports</h3>
                <p class="text-sm text-gray-600">Annual statistics for the last 5 years</p>
            </div>
        </a>
        
        <a href="{{ route('admin.reports.index', ['type' => 'project']) }}" 
           class="bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-green-500 hover:shadow-lg transition-all group">
            <div class="text-center">
                <div class="text-4xl mb-3">📁</div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-600 mb-2">Project Reports</h3>
                <p class="text-sm text-gray-600">Detailed metrics for each project</p>
            </div>
        </a>
    </div>
    @endif

    @if($reportType == 'monthly')
    <!-- Monthly Report Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Last 12 Months Performance</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-600 to-purple-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Month</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Tasks</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Completed</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Work Hours</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($monthlyData as $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">{{ $data['month'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">{{ $data['total_tasks'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">{{ $data['completed_tasks'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['completion_rate'] >= 75)
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @elseif($data['completion_rate'] >= 50)
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">⏱️ {{ $data['work_hours'] }}h</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ route('admin.reports.monthly') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="month" value="{{ $data['month_number'] }}">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Export
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @elseif($reportType == 'yearly')
    <!-- Yearly Report Cards -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Last 5 Years Performance</h2>
            <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($yearlyData as $index => $data)
            <div class="bg-gradient-to-br {{ $index % 4 === 0 ? 'from-purple-500 to-purple-700' : ($index % 4 === 1 ? 'from-blue-500 to-blue-700' : ($index % 4 === 2 ? 'from-green-500 to-green-700' : 'from-orange-500 to-orange-700')) }} rounded-lg shadow-lg text-white p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-3xl font-bold">{{ $data['year'] }}</h3>
                    <span class="px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm font-bold">
                        {{ $data['completion_rate'] }}%
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-white bg-opacity-15 backdrop-blur-lg rounded-lg p-3">
                        <div class="text-xs opacity-75 mb-1">🏗️ Projects</div>
                        <div class="text-2xl font-bold">{{ $data['total_projects'] }}</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-lg rounded-lg p-3">
                        <div class="text-xs opacity-75 mb-1">✅ Completed</div>
                        <div class="text-2xl font-bold">{{ $data['completed_projects'] }}</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-lg rounded-lg p-3">
                        <div class="text-xs opacity-75 mb-1">📋 Tasks</div>
                        <div class="text-2xl font-bold">{{ $data['total_tasks'] }}</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-lg rounded-lg p-3">
                        <div class="text-xs opacity-75 mb-1">✔️ Done</div>
                        <div class="text-2xl font-bold">{{ $data['completed_tasks'] }}</div>
                    </div>
                </div>
                
                <div class="bg-white bg-opacity-15 backdrop-blur-lg rounded-lg p-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">⏱️ Total Work Hours</span>
                        <span class="text-xl font-bold">{{ $data['work_hours'] }}h</span>
                    </div>
                </div>
                
                <form action="{{ route('admin.reports.yearly') }}" method="POST">
                    @csrf
                    <input type="hidden" name="year" value="{{ $data['year'] }}">
                    <button type="submit" class="w-full px-4 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 border-2 border-white border-opacity-30 rounded-lg font-bold transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Full Report
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @elseif($reportType == 'project')
    <!-- Project Report Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">All Projects Overview</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-600 to-purple-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Project</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Leader</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Tasks</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Completed</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Overdue</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Hours</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Team</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($projectData as $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">📁 {{ $data['project_name'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $data['leader_name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['status'] == 'active')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Active</span>
                                @elseif($data['status'] == 'completed')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Completed</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-bold">{{ ucfirst($data['status']) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">{{ $data['total_tasks'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">✅ {{ $data['completed_tasks'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['overdue_tasks'] > 0)
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-bold">⚠️ {{ $data['overdue_tasks'] }}</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-sm font-medium">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['completion_rate'] >= 75)
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @elseif($data['completion_rate'] >= 50)
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-bold">{{ $data['completion_rate'] }}%</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">⏱️ {{ $data['work_hours'] }}h</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">👥 {{ $data['team_members'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ route('admin.reports.project') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $data['project_id'] }}">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Export
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Custom Report Form & Recent Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Custom Report Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-4">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Custom Report
                    </h3>
                    
                    <form id="reportForm" action="{{ route('admin.reports.generate') }}" method="POST">
                        @csrf
                        
                        <!-- Date Range -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Date Range *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="date" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ now()->subMonth()->format('Y-m-d') }}" required>
                                    <span class="text-xs text-gray-500">Start Date</span>
                                </div>
                                <div>
                                    <input type="date" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ now()->format('Y-m-d') }}" required>
                                    <span class="text-xs text-gray-500">End Date</span>
                                </div>
                            </div>
                        </div>

                        <!-- Project Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🏗️ Project <span class="text-gray-400">(Optional)</span></label>
                            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- User Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">👤 User <span class="text-gray-400">(Optional)</span></label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}">{{ $user->full_name }} - {{ ucfirst($user->role) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Status <span class="text-gray-400">(Optional)</span></label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Statuses</option>
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                            <div class="flex">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-blue-800">Report Contents:</p>
                                    <ul class="text-xs text-blue-700 mt-2 space-y-1">
                                        <li>• Project Summary & Statistics</li>
                                        <li>• Task Completion Analysis</li>
                                        <li>• Work Time Tracking Data</li>
                                        <li>• Team Performance Metrics</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <button type="submit" id="generateBtn" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-bold shadow-lg transition-all">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Generate & Download Report
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recent Reports
                    </h3>
                    
                    @if($recentReports->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Filters</th>
                                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Generated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentReports as $report)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                                {{ ucfirst($report->report_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $filters = is_array($report->filters) ? $report->filters : [];
                                            @endphp
                                            <div class="text-xs text-gray-600 space-y-1">
                                                @if(!empty($filters['date_from']) && !empty($filters['date_to']))
                                                    <div>
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ $filters['date_from'] }} to {{ $filters['date_to'] }}
                                                    </div>
                                                @endif
                                                @if(!empty($filters['project_id']))
                                                    <div>
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        {{ $projects->firstWhere('project_id', $filters['project_id'])->project_name ?? 'N/A' }}
                                                    </div>
                                                @endif
                                                @if(!empty($filters['user_id']))
                                                    <div>
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        {{ $users->firstWhere('user_id', $filters['user_id'])->full_name ?? 'N/A' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-semibold text-gray-900">{{ $report->generated_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $report->generated_at->format('H:i') }} • {{ $report->generated_at->diffForHumans() }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📋</div>
                            <p class="text-gray-600 font-semibold">No Reports Yet</p>
                            <p class="text-sm text-gray-500">Generate your first report using the form</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Guide -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <svg class="w-5 h-5 inline mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Quick Guide
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-purple-50 border-l-4 border-purple-500 p-3 rounded">
                            <div class="font-semibold text-purple-800 text-sm mb-1">1️⃣ Select Date Range</div>
                            <p class="text-xs text-purple-700">Choose start and end dates</p>
                        </div>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                            <div class="font-semibold text-blue-800 text-sm mb-1">2️⃣ Apply Filters</div>
                            <p class="text-xs text-blue-700">Filter by project, user, or status</p>
                        </div>
                        <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                            <div class="font-semibold text-green-800 text-sm mb-1">3️⃣ Generate Report</div>
                            <p class="text-xs text-green-700">Download as CSV file</p>
                        </div>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-3 rounded">
                            <div class="font-semibold text-orange-800 text-sm mb-1">4️⃣ Analyze Data</div>
                            <p class="text-xs text-orange-700">Open in Excel or Sheets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Generating...';
    btn.style.opacity = '0.7';
    
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Generate & Download Report';
        btn.style.opacity = '1';
    }, 5000);
});
</script>
@endpush
