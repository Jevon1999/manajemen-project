@extends('layout.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-description', 'Konfigurasi sistem dan pengaturan aplikasi')

@section('content')
<div class="space-y-6">
    <!-- Application Settings -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Application Settings</h3>
            <p class="text-sm text-gray-600">Configure general application settings</p>
        </div>
        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- App Name -->
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-700">Application Name</label>
                <input type="text" name="app_name" id="app_name" value="{{ config('app.name') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- App URL -->
            <div>
                <label for="app_url" class="block text-sm font-medium text-gray-700">Application URL</label>
                <input type="url" name="app_url" id="app_url" value="{{ config('app.url') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Timezone -->
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                <select name="timezone" id="timezone" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="Asia/Jakarta" {{ config('app.timezone') === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta</option>
                    <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ config('app.timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                </select>
            </div>

            <!-- Default User Role -->
            <div>
                <label for="default_role" class="block text-sm font-medium text-gray-700">Default User Role</label>
                <select name="default_role" id="default_role" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="user">User</option>
                    <option value="leader">Leader</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Email Settings -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="100">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Email Settings</h3>
            <p class="text-sm text-gray-600">Configure email notifications and SMTP settings</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Email Driver -->
            <div>
                <label for="mail_driver" class="block text-sm font-medium text-gray-700">Mail Driver</label>
                <select name="mail_driver" id="mail_driver" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="smtp" {{ config('mail.default') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ config('mail.default') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    <option value="log" {{ config('mail.default') === 'log' ? 'selected' : '' }}>Log (Testing)</option>
                </select>
            </div>

            <!-- SMTP Host -->
            <div>
                <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                <input type="text" name="mail_host" id="mail_host" value="{{ config('mail.mailers.smtp.host') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- SMTP Port -->
            <div>
                <label for="mail_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                <input type="number" name="mail_port" id="mail_port" value="{{ config('mail.mailers.smtp.port') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- From Email -->
            <div>
                <label for="mail_from" class="block text-sm font-medium text-gray-700">From Email</label>
                <input type="email" name="mail_from" id="mail_from" value="{{ config('mail.from.address') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Security Settings</h3>
            <p class="text-sm text-gray-600">Configure security and authentication settings</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Session Lifetime -->
            <div>
                <label for="session_lifetime" class="block text-sm font-medium text-gray-700">Session Lifetime (minutes)</label>
                <input type="number" name="session_lifetime" id="session_lifetime" value="{{ config('session.lifetime') }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Password Requirements -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password Requirements</label>
                <div class="mt-2 space-y-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="require_uppercase" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-600">Require uppercase letters</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="require_numbers" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-600">Require numbers</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="require_symbols" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Require symbols</span>
                    </label>
                </div>
            </div>

            <!-- Two Factor Authentication -->
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="enable_2fa" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-600">Enable Two Factor Authentication</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Project Settings -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="300">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Project Settings</h3>
            <p class="text-sm text-gray-600">Configure project management settings</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Default Project Status -->
            <div>
                <label for="default_project_status" class="block text-sm font-medium text-gray-700">Default Project Status</label>
                <select name="default_project_status" id="default_project_status" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="planning">Planning</option>
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                </select>
            </div>

            <!-- Max Projects per Leader -->
            <div>
                <label for="max_projects_per_leader" class="block text-sm font-medium text-gray-700">Max Projects per Leader</label>
                <input type="number" name="max_projects_per_leader" id="max_projects_per_leader" value="5" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Auto Archive Completed Projects -->
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="auto_archive_completed" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-600">Auto archive completed projects after 30 days</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="bg-white shadow rounded-lg" data-aos="fade-up" data-aos-delay="400">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Notification Settings</h3>
            <p class="text-sm text-gray-600">Configure system notifications</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Email Notifications -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Notifications</label>
                <div class="mt-2 space-y-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="notify_new_user" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-600">New user registration</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="notify_new_project" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-600">New project creation</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="notify_task_deadline" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-600">Task deadline reminders</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Settings -->
    <div class="flex justify-end">
        <button type="submit" form="settings-form" 
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Save Settings
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form attribute to first form
    const firstForm = document.querySelector('form');
    if (firstForm) {
        firstForm.setAttribute('id', 'settings-form');
    }
});
</script>
@endsection