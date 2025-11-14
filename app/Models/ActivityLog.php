<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_id',
        'project_id',
        'card_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the project related to this log.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the card related to this log.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    /**
     * Static method to log activity (supports both old and new style)
     */
    public static function logActivity(
        $actionOrUserId = null,
        $entityTypeOrAction = null,
        $entityIdOrEntityType = null,
        $descriptionOrEntityId = null,
        $oldValuesOrProjectId = null,
        $newValuesOrCardId = null,
        $oldValues = null,
        $newValues = null,
        // Named parameters
        $user_id = null,
        $action = null,
        $entity_type = null,
        $entity_id = null,
        $project_id = null,
        $card_id = null,
        $description = null
    ) {
        // Check if using named parameters (new style)
        if ($user_id !== null || $action !== null) {
            return static::create([
                'user_id' => $user_id ?? auth()->id(),
                'project_id' => $project_id,
                'card_id' => $card_id,
                'action' => $action,
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // Old style (positional parameters)
        return static::create([
            'user_id' => auth()->id(),
            'action' => $actionOrUserId,
            'entity_type' => $entityTypeOrAction,
            'entity_id' => $entityIdOrEntityType,
            'description' => $descriptionOrEntityId,
            'old_values' => $oldValuesOrProjectId,
            'new_values' => $newValuesOrCardId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
