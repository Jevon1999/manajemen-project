<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use App\Models\Card;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    use RespondsWithJson;

    /**
     * Get time entries for authenticated user
     */
    public function index(Request $request)
    {
        $query = TimeEntry::where('user_id', $request->user()->user_id)
            ->with(['card.board.project', 'user']);

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }

        // Filter by card
        if ($request->has('card_id')) {
            $query->where('card_id', $request->card_id);
        }

        $entries = $query->orderBy('work_date', 'desc')
            ->paginate($request->integer('per_page', 20));

        return $this->successCollection(TimeEntryResource::collection($entries), 'Time entries retrieved');
    }

    /**
     * Get today's time entries
     */
    public function today(Request $request)
    {
        $entries = TimeEntry::where('user_id', $request->user()->user_id)
            ->whereDate('work_date', today())
            ->with(['card.board.project'])
            ->get();

        $totalHours = $entries->sum('hours_spent');

        return $this->success([
            'entries' => TimeEntryResource::collection($entries),
            'total_hours' => round($totalHours, 2),
            'date' => today()->format('Y-m-d'),
        ], 'Today\'s time entries');
    }

    /**
     * Store a new time entry
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'card_id' => ['required', 'exists:cards,card_id'],
            'work_date' => ['required', 'date', 'before_or_equal:today'],
            'hours_spent' => ['required', 'numeric', 'min:0.1', 'max:24'],
            'description' => ['required', 'string', 'max:1000'],
            'entry_type' => ['nullable', 'in:manual,timer'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        // Check if user is assigned to the card
        $card = Card::findOrFail($data['card_id']);
        $isAssigned = $card->assignments()->where('user_id', $request->user()->user_id)->exists();
        
        if (!$isAssigned) {
            return $this->error('You are not assigned to this task', 403);
        }

        $data['user_id'] = $request->user()->user_id;
        $data['entry_type'] = $data['entry_type'] ?? 'manual';
        $data['is_billable'] = $data['is_billable'] ?? true;

        $entry = TimeEntry::create($data);

        // Update card's has_time_log_today flag
        if ($data['work_date'] === today()->format('Y-m-d')) {
            $card->update(['has_time_log_today' => true]);
        }

        // Log activity
        \App\Models\ActivityLog::logActivity(
            'logged_time',
            'time_entry',
            $entry->time_entry_id,
            "Logged {$data['hours_spent']} hours on task '{$card->title}'"
        );

        return $this->successResource(new TimeEntryResource($entry->load('card')), 'Time entry created successfully', 201);
    }

    /**
     * Start a timer for a task
     */
    public function startTimer(Request $request)
    {
        $data = $request->validate([
            'card_id' => ['required', 'exists:cards,card_id'],
        ]);

        // Check if user is assigned to the card
        $card = Card::findOrFail($data['card_id']);
        $isAssigned = $card->assignments()->where('user_id', $request->user()->user_id)->exists();
        
        if (!$isAssigned) {
            return $this->error('You are not assigned to this task', 403);
        }

        // Check if there's already a running timer
        $runningTimer = TimeEntry::where('user_id', $request->user()->user_id)
            ->where('entry_type', 'timer')
            ->whereNull('ended_at')
            ->first();

        if ($runningTimer) {
            return $this->error('You already have a running timer. Please stop it first.', 422);
        }

        $entry = TimeEntry::create([
            'card_id' => $data['card_id'],
            'user_id' => $request->user()->user_id,
            'work_date' => today(),
            'hours_spent' => 0,
            'entry_type' => 'timer',
            'started_at' => now(),
            'is_billable' => true,
        ]);

        return $this->successResource(new TimeEntryResource($entry->load('card')), 'Timer started', 201);
    }

    /**
     * Stop a running timer
     */
    public function stopTimer(Request $request, $id)
    {
        $entry = TimeEntry::where('time_entry_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->where('entry_type', 'timer')
            ->whereNull('ended_at')
            ->firstOrFail();

        $data = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $entry->ended_at = now();
        $entry->description = $data['description'];
        $entry->calculateDuration();
        $entry->save();

        // Update card's has_time_log_today flag
        $entry->card->update(['has_time_log_today' => true]);

        // Log activity
        \App\Models\ActivityLog::logActivity(
            'stopped_timer',
            'time_entry',
            $entry->time_entry_id,
            "Logged {$entry->hours_spent} hours on task '{$entry->card->title}'"
        );

        return $this->successResource(new TimeEntryResource($entry->load('card')), 'Timer stopped successfully');
    }

    /**
     * Get active timer for authenticated user
     */
    public function activeTimer(Request $request)
    {
        $timer = TimeEntry::where('user_id', $request->user()->user_id)
            ->where('entry_type', 'timer')
            ->whereNull('ended_at')
            ->with('card.board.project')
            ->first();

        if (!$timer) {
            return $this->success(null, 'No active timer');
        }

        // Calculate elapsed time
        $elapsed = now()->diffInMinutes($timer->started_at);
        $timer->elapsed_minutes = $elapsed;
        $timer->elapsed_hours = round($elapsed / 60, 2);

        return $this->successResource(new TimeEntryResource($timer), 'Active timer retrieved');
    }

    /**
     * Update a time entry
     */
    public function update(Request $request, $id)
    {
        $entry = TimeEntry::where('time_entry_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();

        $data = $request->validate([
            'hours_spent' => ['sometimes', 'numeric', 'min:0.1', 'max:24'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'is_billable' => ['sometimes', 'boolean'],
        ]);

        $entry->update($data);

        return $this->successResource(new TimeEntryResource($entry->load('card')), 'Time entry updated');
    }

    /**
     * Delete a time entry
     */
    public function destroy(Request $request, $id)
    {
        $entry = TimeEntry::where('time_entry_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();

        $entry->delete();

        return $this->success(null, 'Time entry deleted');
    }

    /**
     * Get time statistics
     */
    public function statistics(Request $request)
    {
        $userId = $request->user()->user_id;

        $stats = [
            'today' => TimeEntry::where('user_id', $userId)
                ->whereDate('work_date', today())
                ->sum('hours_spent'),
            'yesterday' => TimeEntry::where('user_id', $userId)
                ->whereDate('work_date', today()->subDay())
                ->sum('hours_spent'),
            'this_week' => TimeEntry::where('user_id', $userId)
                ->whereBetween('work_date', [now()->startOfWeek(), now()])
                ->sum('hours_spent'),
            'this_month' => TimeEntry::where('user_id', $userId)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->sum('hours_spent'),
            'total' => TimeEntry::where('user_id', $userId)->sum('hours_spent'),
        ];

        // Round all values
        foreach ($stats as $key => $value) {
            $stats[$key] = round($value, 2);
        }

        return $this->success($stats, 'Time statistics retrieved');
    }
}
