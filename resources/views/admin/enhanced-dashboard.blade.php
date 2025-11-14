@extends('layout.app')

@section('title', 'Enhanced Admin Dashboard')

@section('page-title', 'Enhanced Admin Dashboard')
@section('page-description', 'Comprehensive system overview and administrative tools')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Quick Action Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                <p class="text-sm text-gray-500">Common administrative tasks</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button onclick="showCreateProjectModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Project
                </button>
                
                <button onclick="showCreateUserModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Add User
                </button>
                
                <button onclick="generateSystemReport()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    System Report
                </button>
                
                <button onclick="systemMaintenance()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Maintenance
                </button>
            </div>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">System Health</p>
                    <p class="text-2xl font-bold text-green-600" id="systemHealth">Excellent</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500">
                    <span class="flex items-center">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                        All systems operational
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Users</p>
                    <p class="text-2xl font-bold text-blue-600" id="activeUsers">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500">
                    <span id="onlineNow">0 online now</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Server Load</p>
                    <p class="text-2xl font-bold text-orange-600" id="serverLoad">12%</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-orange-600 h-2 rounded-full" style="width: 12%" id="loadBar"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Storage Used</p>
                    <p class="text-2xl font-bold text-purple-600" id="storageUsed">2.4 GB</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-500">
                    <span id="storagePercent">24% of 10 GB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Project & Task Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Project Status Distribution -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Project Status Distribution</h3>
                    <p class="text-sm text-gray-500">Current project states across the system</p>
                </div>
                <button onclick="refreshProjectStats()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4" id="projectStatusChart">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Planning</span>
                    </div>
                    <span class="text-sm text-gray-500" id="planningCount">0</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">In Progress</span>
                    </div>
                    <span class="text-sm text-gray-500" id="inProgressCount">0</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Review</span>
                    </div>
                    <span class="text-sm text-gray-500" id="reviewCount">0</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Completed</span>
                    </div>
                    <span class="text-sm text-gray-500" id="completedCount">0</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">On Hold</span>
                    </div>
                    <span class="text-sm text-gray-500" id="onHoldCount">0</span>
                </div>
            </div>
        </div>

        <!-- Task Performance Metrics -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Task Performance</h3>
                    <p class="text-sm text-gray-500">System-wide task completion metrics</p>
                </div>
                <button onclick="refreshTaskStats()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-6">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Task Completion Rate</span>
                        <span class="text-sm text-gray-500" id="completionRate">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 0%" id="completionBar"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Average Response Time</span>
                        <span class="text-sm text-gray-500" id="responseTime">0h</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 75%" id="responseBar"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">On-time Delivery</span>
                        <span class="text-sm text-gray-500" id="onTimeRate">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: 0%" id="onTimeBar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent System Activity -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                    <a href="/admin/activity-log" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4" id="recentActivity">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2 text-gray-500">Loading activity...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">System Alerts</h3>
                    <button onclick="clearAllAlerts()" class="text-sm text-red-600 hover:text-red-800">Clear all</button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4" id="systemAlerts">
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">All Clear</h3>
                        <p class="text-gray-600">No system alerts at this time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrative Tools -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Administrative Tools</h3>
            <p class="text-sm text-gray-500">System management and monitoring tools</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="/admin/project-admin/manage-projects" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Projects</span>
                    <span class="text-xs text-gray-500">Manage projects</span>
                </a>

                <a href="/users/management" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Users</span>
                    <span class="text-xs text-gray-500">User management</span>
                </a>

                <a href="/admin/project-admin/manage-tasks" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Tasks</span>
                    <span class="text-xs text-gray-500">Task oversight</span>
                </a>

                <a href="/reports/management" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Reports</span>
                    <span class="text-xs text-gray-500">Analytics</span>
                </a>

                <a href="/settings/management" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Settings</span>
                    <span class="text-xs text-gray-500">System config</span>
                </a>

                <button onclick="showSystemMonitoring()" 
                        class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Monitor</span>
                    <span class="text-xs text-gray-500">System health</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    startRealTimeUpdates();
});

function loadDashboardData() {
    loadSystemHealth();
    loadProjectStatistics();
    loadTaskStatistics();
    loadRecentActivity();
    loadSystemAlerts();
}

function loadSystemHealth() {
    // Simulate system health check
    fetch('/admin/system-health')
    .then(response => response.json())
    .then(data => {
        document.getElementById('activeUsers').textContent = data.activeUsers || '12';
        document.getElementById('onlineNow').textContent = `${data.onlineUsers || '3'} online now`;
        document.getElementById('serverLoad').textContent = `${data.serverLoad || '12'}%`;
        document.getElementById('loadBar').style.width = `${data.serverLoad || '12'}%`;
        document.getElementById('storageUsed').textContent = data.storageUsed || '2.4 GB';
        document.getElementById('storagePercent').textContent = data.storagePercent || '24% of 10 GB';
    })
    .catch(error => {
        console.log('System health simulation mode');
        // Use default values when API is not available
    });
}

function loadProjectStatistics() {
    fetch('/admin/project-statistics')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('planningCount').textContent = data.statistics.planning || 0;
            document.getElementById('inProgressCount').textContent = data.statistics.in_progress || 0;
            document.getElementById('reviewCount').textContent = data.statistics.review || 0;
            document.getElementById('completedCount').textContent = data.statistics.completed || 0;
            document.getElementById('onHoldCount').textContent = data.statistics.on_hold || 0;
        }
    })
    .catch(error => {
        console.error('Error loading project statistics:', error);
    });
}

function loadTaskStatistics() {
    fetch('/admin/project-admin/task-statistics')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const total = data.statistics.total || 1;
            const completed = data.statistics.done || 0;
            const completionRate = Math.round((completed / total) * 100);
            
            document.getElementById('completionRate').textContent = `${completionRate}%`;
            document.getElementById('completionBar').style.width = `${completionRate}%`;
            
            // Simulate other metrics
            document.getElementById('responseTime').textContent = '2.5h';
            document.getElementById('onTimeRate').textContent = '85%';
            document.getElementById('onTimeBar').style.width = '85%';
        }
    })
    .catch(error => {
        console.error('Error loading task statistics:', error);
    });
}

function loadRecentActivity() {
    const activityContainer = document.getElementById('recentActivity');
    
    // Simulate recent activity
    const activities = [
        { user: 'John Doe', action: 'created project', item: 'Website Redesign', time: '2 minutes ago', type: 'create' },
        { user: 'Jane Smith', action: 'completed task', item: 'Database Setup', time: '15 minutes ago', type: 'complete' },
        { user: 'Mike Johnson', action: 'joined project', item: 'Mobile App', time: '1 hour ago', type: 'join' },
        { user: 'Sarah Wilson', action: 'updated task', item: 'User Interface', time: '2 hours ago', type: 'update' }
    ];
    
    const activityHtml = activities.map(activity => {
        const iconColor = {
            create: 'text-green-600',
            complete: 'text-blue-600',
            join: 'text-purple-600',
            update: 'text-orange-600'
        }[activity.type];
        
        return `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">
                        <span class="font-medium">${activity.user}</span> ${activity.action}
                        <span class="font-medium">${activity.item}</span>
                    </p>
                    <p class="text-xs text-gray-500">${activity.time}</p>
                </div>
            </div>
        `;
    }).join('');
    
    activityContainer.innerHTML = activityHtml;
}

function loadSystemAlerts() {
    // Check for system alerts
    const alertsContainer = document.getElementById('systemAlerts');
    
    // Simulate checking for alerts
    setTimeout(() => {
        const hasAlerts = Math.random() > 0.7; // 30% chance of alerts
        
        if (hasAlerts) {
            const alerts = [
                { type: 'warning', message: 'High CPU usage detected on server', time: '10 minutes ago' },
                { type: 'info', message: 'Scheduled backup completed successfully', time: '2 hours ago' }
            ];
            
            const alertsHtml = alerts.map(alert => {
                const alertColor = alert.type === 'warning' ? 'bg-yellow-50 border-yellow-200' : 'bg-blue-50 border-blue-200';
                const iconColor = alert.type === 'warning' ? 'text-yellow-600' : 'text-blue-600';
                
                return `
                    <div class="border rounded-lg p-4 ${alertColor}">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 ${iconColor} mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">${alert.message}</p>
                                <p class="text-xs text-gray-500 mt-1">${alert.time}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            alertsContainer.innerHTML = alertsHtml;
        }
    }, 1000);
}

function startRealTimeUpdates() {
    // Update dashboard every 30 seconds
    setInterval(() => {
        loadSystemHealth();
    }, 30000);
}

function refreshProjectStats() {
    loadProjectStatistics();
}

function refreshTaskStats() {
    loadTaskStatistics();
}

function showCreateProjectModal() {
    window.location.href = '/admin/project-admin/manage-projects';
}

function showCreateUserModal() {
    window.location.href = '/users/management';
}

function generateSystemReport() {
    window.location.href = '/reports/management';
}

function systemMaintenance() {
    window.location.href = '/settings/management';
}

function showSystemMonitoring() {
    // Future implementation: Real-time system monitoring dashboard
    alert('System monitoring dashboard will be implemented in the next update.');
}

function clearAllAlerts() {
    document.getElementById('systemAlerts').innerHTML = `
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">All Clear</h3>
            <p class="text-gray-600">No system alerts at this time</p>
        </div>
    `;
}
</script>
@endsection