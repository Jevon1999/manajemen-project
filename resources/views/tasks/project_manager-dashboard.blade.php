@extends('layout.layout')

@section('title', 'Leader Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Team Lead Dashboard</h1>
        <p class="text-gray-600 text-sm">Ringkasan progress semua proyek yang Anda pimpin.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm text-gray-500">Active Projects</div>
            <div class="text-2xl font-semibold">{{ $stats['active_projects'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm text-gray-500">Active Tasks</div>
            <div class="text-2xl font-semibold">{{ $stats['active_tasks'] ?? 0 }}</div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm text-gray-500">Completed Tasks</div>
            <div class="text-2xl font-semibold">{{ $stats['completed_tasks'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white border rounded-lg">
        <div class="px-4 py-3 border-b font-medium">Latest Tasks</div>
        <div class="divide-y">
            @forelse($tasks as $task)
            <a href="{{ route('tasks.show', $task->card_id) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                <div>
                    <div class="font-medium text-gray-900">{{ $task->card_title ?? $task->title }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $task->board->project->name ?? $task->project_name ?? 'Project' }} • Due: {{ optional($task->due_date)->format('d M Y') }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100">{{ strtoupper($task->status) }}</span>
                    @if(!empty($task->priority))
                    <span class="text-xs px-2 py-1 rounded-full @if($task->priority==='high') bg-red-100 text-red-700 @elseif($task->priority==='medium') bg-yellow-100 text-yellow-700 @else bg-green-100 text-green-700 @endif">{{ ucfirst($task->priority) }}</span>
                    @endif
                </div>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-gray-500">Belum ada task.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
