<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubtaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->subtask_id,
            'card_id' => $this->card_id,
            'title' => $this->subtaks_title, // field name in DB
            'description' => $this->description,
            'status' => $this->status,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'position' => $this->position,
        ];
    }
}
