<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->timelog_id,
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'start_time' => optional($this->start_time)->toISOString(),
            'end_time' => optional($this->end_time)->toISOString(),
            'duration_seconds' => $this->duration_seconds,
            'duration_minutes' => $this->duration_seconds ? round($this->duration_seconds / 60, 2) : null,
            'notes' => $this->notes,
        ];
    }
}
