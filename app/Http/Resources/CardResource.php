<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->card_id,
            'board_id' => $this->board_id,
            'title' => $this->card_title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => optional($this->due_date)->toDateString(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
        ];
    }
}
