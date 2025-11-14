@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Main Content -->
    <div class="flex-1 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-800">My Cards</h1>
            <p class="text-gray-600">Kelola dan pantau card yang ditugaskan kepada Anda</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Cards -->
            <div class="bg-blue-500 rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Total Cards</h3>
                <p class="text-4xl font-bold">{{ $totalCards ?? 0 }}</p>
            </div>

            <!-- TODO -->
            <div class="bg-gray-600 rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">TODO</h3>
                <p class="text-4xl font-bold">{{ $todoCards ?? 0 }}</p>
            </div>

            <!-- In Progress -->
            <div class="bg-purple-500 rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">In Progress</h3>
                <p class="text-4xl font-bold">{{ $inProgressCards ?? 0 }}</p>
            </div>

            <!-- Review -->
            <div class="bg-orange-500 rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Review</h3>
                <p class="text-4xl font-bold">{{ $reviewCards ?? 0 }}</p>
            </div>

            <!-- Done -->
            <div class="bg-green-500 rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Done</h3>
                <p class="text-4xl font-bold">{{ $doneCards ?? 0 }}</p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg p-6 mb-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <input type="text" name="search" placeholder="Cari judul atau deskripsi..." 
                           class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Semua Status</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <select name="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Semua Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <!-- Filter Button -->
            <div class="mt-4 flex justify-end">
                <button type="button" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Filter
                </button>
            </div>
        </div>

        <!-- Cards List -->
        <div class="bg-white rounded-lg shadow-sm">
            @if(isset($cards) && count($cards) > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($cards as $card)
                        <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $card->card_title }}</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ $card->description }}</p>
                                    <div class="mt-2 flex items-center space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $card->status === 'todo' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $card->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $card->status === 'review' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $card->status === 'done' ? 'bg-green-100 text-green-800' : '' }}">
                                            {{ ucfirst($card->status) }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $card->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $card->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $card->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($card->priority) }} Priority
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <button class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Card</h3>
                    <p class="text-gray-500">Anda belum memiliki card yang ditugaskan. Card akan muncul di sini setelah ditugaskan kepada Anda.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection