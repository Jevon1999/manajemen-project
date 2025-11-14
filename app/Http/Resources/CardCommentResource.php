<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comment_id' => $this->comment_id,
            'card_id' => $this->card_id,
            'user_id' => $this->user_id,
            'comment' => $this->comment,
            'is_progress_update' => (bool) $this->is_progress_update,
            'progress_percentage' => $this->progress_percentage,
            'comment_type' => $this->comment_type,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Human-readable timestamps
            'created_at_human' => $this->created_at->diffForHumans(),
            
            // Relationships
            'user' => $this->whenLoaded('user', function() {
                return [
                    'user_id' => $this->user->user_id,
                    'full_name' => $this->user->full_name,
                    'username' => $this->user->username,
                    'avatar' => $this->user->avatar,
                    'role' => $this->user->role,
                    'specialty' => $this->user->specialty,
                ];
            }),
            'replies' => CardCommentResource::collection($this->whenLoaded('replies')),
            'parent' => new CardCommentResource($this->whenLoaded('parent')),
        ];
    }
}
