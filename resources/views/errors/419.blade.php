@extends('layout.error')

@section('title', 'Page Expired')
@section('code', '419')
@section('message', 'Page Expired')

@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Session Expired</h1>
        <p class="text-gray-600 mb-6">Your session has expired due to inactivity. This is a security measure to protect your account.</p>
        
        <div class="space-y-3">
            <button onclick="window.history.back()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Try Again
            </button>
            
            <a href="{{ route('login') }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                Go to Login
            </a>
            
            <a href="{{ route('dashboard') }}" class="block w-full text-blue-600 hover:text-blue-700 font-medium py-2 transition-colors">
                Return to Dashboard
            </a>
        </div>
    </div>
    
    <div class="text-sm text-gray-500">
        <p>If you continue to experience this issue, try:</p>
        <ul class="mt-2 space-y-1">
            <li>• Refreshing your browser</li>
            <li>• Clearing your browser cache</li>
            <li>• Logging out and back in</li>
        </ul>
    </div>
</div>

<script>
// Auto refresh the page after 3 seconds if user doesn't interact
setTimeout(function() {
    if (document.hasFocus()) {
        window.location.reload();
    }
}, 3000);
</script>
@endsection