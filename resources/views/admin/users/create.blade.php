@extends('layout.app')

@section('title', 'Create User')
@section('page-title', 'Create User')
@section('page-description', 'Tambah pengguna baru ke sistem')

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-0">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden" data-aos="fade-up">
        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">User Information</h3>
            <p class="text-xs sm:text-sm text-gray-600">Fill in the details for the new user</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="px-4 sm:px-6 py-4 sm:py-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" 
                           class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-300 @enderror" 
                           required>
                    @error('username')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror" 
                           required>
                    @error('email')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Full Name -->
                <div class="md:col-span-2">
                    <label for="full_name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Full Name</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" 
                           class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('full_name') border-red-300 @enderror" 
                           required>
                    @error('full_name')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                           class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @enderror" 
                           required>
                    @error('password')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Role</label>
                    <select name="role" id="role" 
                            class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-300 @enderror" 
                            required>
                        <option value="">Select Role</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="leader" {{ old('role') === 'leader' ? 'selected' : '' }}>Leader</option>
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Status</label>
                    <select name="status" id="status" 
                            class="block w-full px-3 py-2 sm:px-4 sm:py-2.5 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-300 @enderror" 
                            required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 sm:pt-6 mt-4 sm:mt-6 border-t border-gray-200">
                <a href="{{ route('admin.users') }}" 
                   class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 sm:py-2.5 border border-gray-300 shadow-sm text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 sm:py-2.5 border border-transparent text-xs sm:text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection