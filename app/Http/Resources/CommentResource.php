<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->comment_id,
            'card_id' => $this->card_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'text' => $this->comment_text,
            'type' => $this->comment_type,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
