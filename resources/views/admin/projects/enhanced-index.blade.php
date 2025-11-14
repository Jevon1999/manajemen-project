@extends('layout.app')

@section('title', 'Admin Dashboard - Project Management')

@section('page-title', 'Admin Dashboard')
@section('page-description', 'Comprehensive project management control center with integrated user and system management.')

@section('content')
@php
    $sortOption = request('sort', 'name_asc');
    $searchTerm = trim((string) request('q', ''));
    $baseCollection = $projects instanceof \Illuminate\Support\Collection ? $projects : collect($projects);

    $projectCollection = $baseCollection->when($searchTerm !== '', function ($collection) use ($searchTerm) {
        $needle = mb_strtolower($searchTerm);
        return $collection->filter(function ($project) use ($needle) {
            $haystacks = [
                $project->project_name ?? '',
                $project->description ?? '',
                $project->status ?? '',
                $project->priority ?? '',
            ];
            if ($project->relationLoaded('creator') && $project->creator) {
                $haystacks[] = $project->creator->full_name ?? '';
                $haystacks[] = $project->creator->username ?? '';
            }
            foreach ($haystacks as $value) {
                if ($value !== '' && mb_stripos((string) $value, $needle) !== false) {
                    return true;
                }
            }
            return false;
        });
    })->values();

    $sortedProjects = match ($sortOption) {
        'name_desc' => $projectCollection->sortByDesc(fn ($project) => strtolower($project->project_name ?? '')),
        'created_asc' => $projectCollection->sortBy(fn ($project) => $project->created_at),
        'created_desc' => $projectCollection->sortByDesc(fn ($project) => $project->created_at),
        default => $projectCollection->sortBy(fn ($project) => strtolower($project->project_name ?? '')),
    };

    $sortedProjects = $sortedProjects->values();
    $totalProjects = $sortedProjects->count();
    $totalMembers = $sortedProjects->sum(fn ($project) => $project->members_count ?? 0);
    $averageCompletion = $totalProjects > 0 ? round($sortedProjects->avg(fn ($project) => $project->completion_percentage ?? 0)) : null;

    $statusCounts = [
        'planning' => $projectCollection->filter(fn ($project) => strtolower($project->status ?? '') === 'planning')->count(),
        'active' => $projectCollection->filter(fn ($project) => strtolower($project->status ?? '') === 'active')->count(),
        'completed' => $projectCollection->filter(fn ($project) => strtolower($project->status ?? '') === 'completed')->count(),
    ];

    $priorityCounts = [
        'critical' => $projectCollection->filter(fn ($project) => strtolower($project->priority ?? '') === 'critical')->count(),
        'high' => $projectCollection->filter(fn ($project) => strtolower($project->priority ?? '') === 'high')->count(),
        'medium' => $projectCollection->filter(fn ($project) => strtolower($project->priority ?? '') === 'medium')->count(),
    ];

    $highAttentionProjects = ($priorityCounts['critical'] ?? 0) + ($priorityCounts['high'] ?? 0);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Admin Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">System Administration Dashboard</h1>
            <p class="mt-2 text-sm text-slate-500">
                Complete project management and system oversight in one consolidated interface.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:shadow-xl transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"></path>
                </svg>
                New Project
            </a>
            <a href="{{ route('users.management') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-5 py-2 text-sm font-semibold text-white shadow-lg hover:shadow-xl transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                Manage Users
            </a>
        </div>
    </div>

    <!-- Quick Admin Actions -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-8">
        <!-- Project Management -->
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Projects</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($totalProjects) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $statusCounts['completed'] ?? 0 }} completed</p>
                </div>
                <a href="{{ route('projects.create') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- User Management -->
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 to-red-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Active Users</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($totalMembers) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Across all projects</p>
                </div>
                <a href="{{ route('users.management') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-orange-50 text-orange-600 hover:bg-orange-100 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Leader Management -->
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-yellow-500 to-amber-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Team Leaders</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($statusCounts['active'] ?? 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Active assignments</p>
                </div>
                <a href="{{ route('admin.leaders.management') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- System Reports -->
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">High Priority</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($highAttentionProjects) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Needs attention</p>
                </div>
                <a href="{{ route('reports.management') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 012-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="mb-8 rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Quick Actions</h2>
            <span class="text-sm text-slate-500">Administrative shortcuts</span>
        </div>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('projects.create') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"></path>
                    </svg>
                    Create New Project
                </span>
                <svg class="h-4 w-4 text-white/80 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('users.management') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-gradient-to-r from-orange-500 to-red-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Add User
                </span>
                <svg class="h-4 w-4 text-white/80 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('admin.leaders.management') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-gradient-to-r from-yellow-500 to-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                    </svg>
                    Assign Leader
                </span>
                <svg class="h-4 w-4 text-white/80 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('reports.management') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Generate Report
                </span>
                <svg class="h-4 w-4 text-white/80 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <form method="GET" class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/30 md:max-w-md">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"></path>
            </svg>
            <input type="hidden" name="sort" value="{{ $sortOption }}" />
            <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Search projects, users, tasks..." class="w-full border-none bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" />
        </form>
        <form method="GET" class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 shadow-sm">
            <input type="hidden" name="q" value="{{ $searchTerm }}" />
            <label for="sort" class="text-sm font-medium text-slate-600">Sort by</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="min-w-[160px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:outline-none">
                <option value="name_asc" @selected($sortOption === 'name_asc')>Name (A → Z)</option>
                <option value="name_desc" @selected($sortOption === 'name_desc')>Name (Z → A)</option>
                <option value="created_desc" @selected($sortOption === 'created_desc')>Newest</option>
                <option value="created_asc" @selected($sortOption === 'created_asc')>Oldest</option>
            </select>
        </form>
    </div>

    <!-- Project Grid -->
    <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-slate-900">Project Management</h2>
            <span class="text-sm text-slate-500">{{ $totalProjects }} total projects</span>
        </div>
        
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($sortedProjects as $project)
                @php
                    $statusKey = strtolower($project->status ?? 'planning');
                    $statusClasses = [
                        'planning' => 'bg-sky-100 text-sky-700',
                        'active' => 'bg-emerald-100 text-emerald-700',
                        'completed' => 'bg-violet-100 text-violet-700',
                        'on-hold' => 'bg-amber-100 text-amber-700',
                    ];
                    $statusClass = $statusClasses[$statusKey] ?? $statusClasses['planning'];
                    $completion = $project->completion_percentage ?? 0;
                    $membersCount = $project->members_count ?? 0;
                @endphp

                <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm ring-1 ring-slate-100/70 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $project->project_name ?? 'Unnamed Project' }}</h3>
                                <p class="mt-1 text-sm text-slate-500 line-clamp-2">
                                    {{ $project->description ? \Illuminate\Support\Str::limit(strip_tags($project->description), 100) : 'No description available.' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst(str_replace('-', ' ', $project->status ?? 'Planning')) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Progress</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($completion) }}%</p>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-200">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $completion }}%;"></div>
                                </div>
                            </div>
                            <div class="border-l border-slate-200/80 pl-3">
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Members</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $membersCount }}</p>
                            </div>
                            <div class="border-l border-slate-200/80 pl-3">
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Created</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ optional($project->created_at)->format('M d') ?? '−' }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('projects.show', $project->project_id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View
                            </a>
                            <a href="{{ route('projects.edit', $project->project_id) }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:shadow-lg">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.652a1.875 1.875 0 112.652 2.652l-9.193 9.192a4.5 4.5 0 01-1.591 1.01l-3.294 1.098a.75.75 0 01-.95-.95l1.098-3.294a4.5 4.5 0 011.01-1.59l9.193-9.193z"/>
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('projects.destroy', $project->project_id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Delete this project? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-600 transition hover:border-rose-300 hover:bg-rose-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75v6.75m4.5-6.75v6.75M4.5 6.75h15M6 9l1 12.75a1.5 1.5 0 001.494 1.25h6.012A1.5 1.5 0 0016.5 21.75L17.5 9"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white/70 py-16 text-center">
                    <div class="rounded-full bg-indigo-50 p-4">
                        <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">No Projects Found</h3>
                    <p class="mt-2 max-w-sm text-sm text-slate-500">Start by creating your first project to organize teams and tasks.</p>
                    <a href="{{ route('projects.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-indigo-500 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                        </svg>
                        Create First Project
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced search functionality
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Real-time search feedback (optional)
            const value = this.value.toLowerCase();
            // Could add live filtering here if needed
        });
    }

    // Quick action animations
    const quickActions = document.querySelectorAll('a[class*="hover:-translate-y"]');
    quickActions.forEach(action => {
        action.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        action.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>
@endpush