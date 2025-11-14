@extends('layout.app')

@section('title', 'Leaderboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🏆 Leaderboard</h1>
        <p class="text-gray-600">Top performers based on task completion, work hours, and priority</p>
    </div>

    <!-- Month Selector -->
    <div class="mb-6 flex items-center space-x-4">
        <label for="month-selector" class="text-sm font-medium text-gray-700">Select Month:</label>
        <input 
            type="month" 
            id="month-selector" 
            value="{{ $month }}"
            max="{{ now()->format('Y-m') }}"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            onchange="window.location.href = '{{ route('leaderboard.index') }}?month=' + this.value"
        >
    </div>

    <!-- My Stats Card (if user is logged in) -->
    @if($myStats && $myStats['rank'])
    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Your Stats</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Rank</p>
                        <p class="text-2xl font-bold text-blue-600">
                            #{{ $myStats['rank'] }}
                            @if($myStats['rank'] == 1) 🥇
                            @elseif($myStats['rank'] == 2) 🥈
                            @elseif($myStats['rank'] == 3) 🥉
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Score</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $myStats['total_score'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tasks Done</p>
                        <p class="text-2xl font-bold text-green-600">{{ $myStats['tasks_completed'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Work Hours</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $myStats['work_hours'] }}h</p>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Out of {{ $myStats['total_users'] }} users</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Leaderboard Table -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Rank
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Tasks
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Work Hours
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Total Score
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Details
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leaderboard as $user)
                <tr class="{{ Auth::check() && $user['user_id'] == Auth::id() ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    <!-- Rank -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-gray-700">{{ $user['rank'] }}</span>
                            @if($user['rank'] == 1)
                                <span class="ml-2 text-2xl">🥇</span>
                            @elseif($user['rank'] == 2)
                                <span class="ml-2 text-2xl">🥈</span>
                            @elseif($user['rank'] == 3)
                                <span class="ml-2 text-2xl">🥉</span>
                            @endif
                        </div>
                    </td>

                    <!-- User Info -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($user['name'], 0, 1)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $user['name'] }}
                                    @if(Auth::check() && $user['user_id'] == Auth::id())
                                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">You</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ ucfirst($user['role']) }}
                                    @if($user['specialty'])
                                        • {{ ucfirst($user['specialty']) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Tasks -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-lg font-semibold text-green-600">{{ $user['tasks_completed'] }}</span>
                    </td>

                    <!-- Work Hours -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-lg font-semibold text-purple-600">{{ $user['work_hours'] }}h</span>
                    </td>

                    <!-- Total Score -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-xl font-bold text-indigo-600">{{ $user['total_score'] }}</span>
                    </td>

                    <!-- Details Button -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button 
                            onclick='showDetails(@json($user))'
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                        >
                            View Breakdown
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        No activity in this month
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Scoring Legend -->
    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Scoring System</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600 mb-1">Task Completed</div>
                <div class="text-xl font-bold text-green-600">+50 pts</div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600 mb-1">Priority Bonus</div>
                <div class="text-sm font-semibold text-gray-700">
                    High: +20<br>
                    Medium: +10<br>
                    Low: +5
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600 mb-1">Work Hours</div>
                <div class="text-xl font-bold text-purple-600">+2 pts/h</div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600 mb-1">On-Time Completion</div>
                <div class="text-xl font-bold text-blue-600">+25 pts</div>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="details-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Score Breakdown</h3>
            <button onclick="closeDetails()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="modal-content" class="space-y-3">
            <!-- Content will be inserted by JavaScript -->
        </div>
    </div>
</div>

<script>
function showDetails(user) {
    const modal = document.getElementById('details-modal');
    const content = document.getElementById('modal-content');
    
    content.innerHTML = `
        <div class="border-b pb-3">
            <p class="text-sm text-gray-600">User</p>
            <p class="text-lg font-semibold text-gray-800">${user.name}</p>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Task Points (${user.tasks_completed} × 50)</span>
                <span class="font-semibold text-green-600">+${user.task_points}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Priority Bonus</span>
                <span class="font-semibold text-orange-600">+${user.priority_bonus}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Work Points (${user.work_hours}h × 2)</span>
                <span class="font-semibold text-purple-600">+${user.work_points}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">On-Time Bonus</span>
                <span class="font-semibold text-blue-600">+${user.on_time_bonus}</span>
            </div>
        </div>
        <div class="border-t pt-3 mt-3">
            <div class="flex justify-between items-center">
                <span class="text-base font-semibold text-gray-800">Total Score</span>
                <span class="text-xl font-bold text-indigo-600">${user.total_score}</span>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeDetails() {
    document.getElementById('details-modal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('details-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetails();
    }
});
</script>
@endsection
