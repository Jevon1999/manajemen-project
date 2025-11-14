{{-- Simplified Sidebar for Leader Dashboard --}}
<aside class="w-64 bg-gradient-to-b from-indigo-900 to-purple-900 text-white flex flex-col shadow-xl">
    <!-- Header -->
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-users-cog text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg">Leader Panel</h1>
                <p class="text-gray-300 text-sm">{{ Auth::user()->full_name }}</p>
            </div>
        </div>
    </div>

    <!-- Leader Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('leader.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-indigo-700 text-white">
            <i class="fas fa-tachometer-alt mr-3"></i>
            Dashboard
        </a>

        <!-- 4 Core Functions -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                Core Functions
            </p>
            
            <!-- Assign Tasks -->
            <a href="#assign-tasks" onclick="scrollToSection('assign-tasks')" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors text-gray-300">
                <i class="fas fa-user-plus mr-3 text-green-400"></i>
                Assign Tasks
            </a>
            
            <!-- Set Priority -->
            <a href="#set-priority" onclick="scrollToSection('set-priority')" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors text-gray-300">
                <i class="fas fa-exclamation-triangle mr-3 text-yellow-400"></i>
                Set Priority
            </a>
            
            <!-- Update Status -->
            <a href="#update-status" onclick="scrollToSection('update-status')" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors text-gray-300">
                <i class="fas fa-tasks mr-3 text-blue-400"></i>
                Update Status
            </a>
            
            <!-- View Progress -->
            <a href="#view-progress" onclick="scrollToSection('view-progress')" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors text-gray-300">
                <i class="fas fa-chart-line mr-3 text-purple-400"></i>
                View Progress
            </a>
        </div>

        <!-- Quick Links -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                Quick Access
            </p>
            <a href="{{ route('extension-requests.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors text-gray-300 relative">
                <i class="fas fa-clock mr-3 text-orange-400"></i>
                Extension Requests
                @php
                    $pendingCount = \App\Models\ExtensionRequest::whereHas('card.board.project', function($q) {
                        $q->where('leader_id', Auth::id());
                    })->where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto bg-orange-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('leader.projects') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors text-gray-300">
                <i class="fas fa-project-diagram mr-3 text-gray-400"></i>
                My Projects
            </a>
            <a href="{{ route('boards.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors text-gray-300">
                <i class="fas fa-columns mr-3 text-gray-400"></i>
                Project Boards
            </a>
        </div>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-gray-700">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-300 rounded-lg hover:bg-red-600 hover:text-white transition-colors">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Logout
            </button>
        </form>
    </div>
</aside>

<script>
function scrollToSection(sectionId) {
    document.getElementById(sectionId).scrollIntoView({ 
        behavior: 'smooth' 
    });
}
</script>