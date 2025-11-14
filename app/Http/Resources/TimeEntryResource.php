<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'time_entry_id' => $this->time_entry_id,
            'card_id' => $this->card_id,
            'user_id' => $this->user_id,
            'work_date' => $this->work_date->format('Y-m-d'),
            'hours_spent' => (float) $this->hours_spent,
            'description' => $this->description,
            'entry_type' => $this->entry_type,
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'is_billable' => (bool) $this->is_billable,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'card' => $this->whenLoaded('card', function() {
                return [
                    'card_id' => $this->card->card_id,
                    'title' => $this->card->title,
                    'status' => $this->card->status,
                    'project' => $this->card->board?->project ? [
                        'project_id' => $this->card->board->project->project_id,
                        'project_name' => $this->card->board->project->project_name,
                    ] : null,
                ];
            }),
            'user' => $this->whenLoaded('user', function() {
                return [
                    'user_id' => $this->user->user_id,
                    'full_name' => $this->user->full_name,
                    'username' => $this->user->username,
                    'avatar' => $this->user->avatar,
                ];
            }),
        ];
    }
}
