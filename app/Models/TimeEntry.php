<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $table = 'time_entries';
    protected $primaryKey = 'time_entry_id';

    protected $fillable = [
        'card_id',
        'user_id',
        'work_date',
        'hours_spent',
        'description',
        'entry_type',
        'started_at',
        'ended_at',
        'is_billable',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours_spent' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_billable' => 'boolean',
    ];

    /**
     * Get the card that owns the time entry.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    /**
     * Get the user that created the time entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Calculate duration from started_at and ended_at if using timer
     */
    public function calculateDuration()
    {
        if ($this->entry_type === 'timer' && $this->started_at && $this->ended_at) {
            $minutes = $this->ended_at->diffInMinutes($this->started_at);
            $this->hours_spent = round($minutes / 60, 2);
            return $this->hours_spent;
        }
        return $this->hours_spent;
    }
}
