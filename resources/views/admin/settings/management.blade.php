@extends('layout.app')

@section('title', 'System Settings Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    System Settings
                </h1>
                <p class="text-gray-600 mt-2">Kelola konfigurasi sistem, keamanan, dan pengaturan aplikasi</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="getSystemInfo()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    System Info
                </button>
            </div>
        </div>
    </div>

    <!-- System Health Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $settings['general']['app_name'] }}</h3>
                    <p class="text-gray-600">System Status: Active</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $settings['database']['database_connection'] }}</h3>
                    <p class="text-gray-600">Database Connected</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ count($settings['backup']['recent_backups']) }}</h3>
                    <p class="text-gray-600">Recent Backups</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $settings['performance']['cache_driver'] }}</h3>
                    <p class="text-gray-600">Cache System</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button onclick="showTab('general')" id="tab-general" class="tab-button active py-4 px-6 border-b-2 border-gray-500 text-gray-600 font-medium text-sm">
                    General Settings
                </button>
                <button onclick="showTab('email')" id="tab-email" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Email Configuration
                </button>
                <button onclick="showTab('security')" id="tab-security" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Security
                </button>
                <button onclick="showTab('backup')" id="tab-backup" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Backup & Recovery
                </button>
                <button onclick="showTab('performance')" id="tab-performance" class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Performance
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div id="content-general" class="tab-content p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">General Application Settings</h2>
            
            <form id="generalSettingsForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
                        <input type="text" id="app_name" value="{{ $settings['general']['app_name'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Application URL</label>
                        <input type="url" id="app_url" value="{{ $settings['general']['app_url'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select id="timezone" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                            <option value="Asia/Jakarta" {{ $settings['general']['timezone'] === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta</option>
                            <option value="UTC" {{ $settings['general']['timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ $settings['general']['timezone'] === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <select id="locale" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                            <option value="en" {{ $settings['general']['locale'] === 'en' ? 'selected' : '' }}>English</option>
                            <option value="id" {{ $settings['general']['locale'] === 'id' ? 'selected' : '' }}>Indonesian</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Save General Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Email Configuration Tab -->
        <div id="content-email" class="tab-content p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Email Configuration</h2>
            
            <form id="emailSettingsForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mail Host</label>
                        <input type="text" id="mail_host" value="{{ $settings['email']['mail_host'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mail Port</label>
                        <input type="number" id="mail_port" value="{{ $settings['email']['mail_port'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="mail_username" placeholder="SMTP Username" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="mail_password" placeholder="SMTP Password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                        <input type="email" id="mail_from_address" value="{{ $settings['email']['mail_from_address'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" id="mail_from_name" value="{{ $settings['email']['mail_from_name'] }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                </div>
                
                <div class="flex justify-between">
                    <button type="button" onclick="testEmail()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Test Email
                    </button>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Save Email Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Tab -->
        <div id="content-security" class="tab-content p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Security Settings</h2>
            
            <form id="securitySettingsForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Lifetime (minutes)</label>
                        <input type="number" id="session_lifetime" value="{{ $settings['security']['session_lifetime'] }}" min="1" max="10080" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        <p class="text-sm text-gray-500 mt-1">Maximum 10080 minutes (1 week)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Timeout (minutes)</label>
                        <input type="number" id="password_timeout" value="{{ $settings['security']['password_timeout'] }}" min="1" max="43200" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        <p class="text-sm text-gray-500 mt-1">Maximum 43200 minutes (12 hours)</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-3">Security Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Session Driver:</label>
                            <span class="ml-2 font-medium">{{ $settings['security']['session_driver'] }}</span>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Encryption Cipher:</label>
                            <span class="ml-2 font-medium">{{ $settings['security']['encryption_cipher'] }}</span>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">CSRF Protection:</label>
                            <span class="ml-2 font-medium text-green-600">{{ $settings['security']['csrf_protection'] ? 'Enabled' : 'Disabled' }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Save Security Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Backup & Recovery Tab -->
        <div id="content-backup" class="tab-content p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Backup & Recovery</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Backup Controls -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Backup Controls</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-600">Backup Schedule:</label>
                            <span class="ml-2 font-medium">{{ ucfirst($settings['backup']['backup_schedule']) }}</span>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Retention Period:</label>
                            <span class="ml-2 font-medium">{{ $settings['backup']['backup_retention'] }} days</span>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Storage Location:</label>
                            <span class="ml-2 font-medium">{{ ucfirst($settings['backup']['backup_storage']) }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-3">
                        <button onclick="createBackup()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Create Backup Now
                        </button>
                        <button onclick="clearCache()" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Clear Application Cache
                        </button>
                    </div>
                </div>
                
                <!-- Recent Backups -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Backups</h3>
                    
                    <div class="space-y-3">
                        @forelse($settings['backup']['recent_backups'] as $backup)
                        <div class="flex items-center justify-between p-3 bg-white rounded border">
                            <div>
                                <p class="font-medium text-gray-900">{{ $backup['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $backup['date'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</p>
                                <button class="text-blue-600 hover:text-blue-800 text-sm">Download</button>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 italic">No recent backups found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Tab -->
        <div id="content-performance" class="tab-content p-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Performance Settings</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cache Settings -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cache Configuration</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-600">Cache Driver:</label>
                            <span class="ml-2 font-medium">{{ $settings['performance']['cache_driver'] }}</span>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Queue Driver:</label>
                            <span class="ml-2 font-medium">{{ $settings['performance']['queue_driver'] }}</span>
                        </div>
                        @if($settings['performance']['redis_connection'])
                        <div>
                            <label class="text-sm text-gray-600">Redis:</label>
                            <span class="ml-2 font-medium text-green-600">Connected ({{ $settings['performance']['redis_connection'] }})</span>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-gray-600">OPCache:</label>
                            <span class="ml-2 font-medium {{ $settings['performance']['opcache_enabled'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $settings['performance']['opcache_enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Actions -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Optimization Tools</h3>
                    
                    <div class="space-y-3">
                        <button onclick="clearCache()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Clear All Cache
                        </button>
                        <button onclick="optimizeDatabase()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Optimize Database
                        </button>
                        <button onclick="generateSitemap()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Generate Sitemap
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info Modal -->
<div id="systemInfoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
                <button onclick="closeSystemInfoModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="systemInfoContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-gray-500', 'text-gray-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-gray-500', 'text-gray-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

// Settings form submissions
document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        app_name: document.getElementById('app_name').value,
        timezone: document.getElementById('timezone').value,
        locale: document.getElementById('locale').value,
    };

    fetch('/api/settings/general', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Settings updated successfully');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating settings');
    });
});

document.getElementById('emailSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        mail_host: document.getElementById('mail_host').value,
        mail_port: document.getElementById('mail_port').value,
        mail_username: document.getElementById('mail_username').value,
        mail_password: document.getElementById('mail_password').value,
        mail_from_address: document.getElementById('mail_from_address').value,
        mail_from_name: document.getElementById('mail_from_name').value,
    };

    fetch('/api/settings/email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Email settings updated successfully');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating email settings');
    });
});

document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        session_lifetime: document.getElementById('session_lifetime').value,
        password_timeout: document.getElementById('password_timeout').value,
    };

    fetch('/api/settings/security', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Security settings updated successfully');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating security settings');
    });
});

// Utility functions
function testEmail() {
    const testEmail = prompt('Enter email address to send test email:');
    if (testEmail) {
        fetch('/api/settings/test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                test_email: testEmail
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Test email sent successfully');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending test email');
        });
    }
}

function createBackup() {
    if (confirm('Create a new database backup? This may take a few minutes.')) {
        fetch('/api/settings/backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Backup created successfully');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating backup');
        });
    }
}

function clearCache() {
    if (confirm('Clear all application cache? This will temporarily slow down the application.')) {
        fetch('/api/settings/clear-cache', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Cache cleared successfully');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while clearing cache');
        });
    }
}

function optimizeDatabase() {
    alert('Database optimization feature will be implemented');
}

function generateSitemap() {
    alert('Sitemap generation feature will be implemented');
}

function getSystemInfo() {
    document.getElementById('systemInfoModal').classList.remove('hidden');
    document.getElementById('systemInfoModal').classList.add('flex');
    
    fetch('/api/settings/system-info')
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('systemInfoContent');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><strong>PHP Version:</strong> ${data.php_version}</div>
                    <div><strong>Laravel Version:</strong> ${data.laravel_version}</div>
                    <div><strong>Server Software:</strong> ${data.server_software}</div>
                    <div><strong>Database Version:</strong> ${data.database_version}</div>
                    <div><strong>Memory Limit:</strong> ${data.memory_limit}</div>
                    <div><strong>Max Execution Time:</strong> ${data.max_execution_time}s</div>
                    <div><strong>Upload Max Size:</strong> ${data.upload_max_filesize}</div>
                    <div><strong>Free Disk Space:</strong> ${Math.round(data.disk_space.free / 1024 / 1024 / 1024)} GB</div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('systemInfoContent').innerHTML = '<p class="text-red-600">Error loading system information</p>';
        });
}

function closeSystemInfoModal() {
    document.getElementById('systemInfoModal').classList.add('hidden');
    document.getElementById('systemInfoModal').classList.remove('flex');
}
</script>
@endpush

<style>
.tab-button.active {
    border-color: #6b7280 !important;
    color: #6b7280 !important;
}

.tab-button:hover:not(.active) {
    color: #374151;
}
</style>
@endsection