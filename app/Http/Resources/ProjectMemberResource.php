<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->member_id,
            'project_id' => $this->project_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'role' => $this->role,
            'joined_at' => optional($this->joined_at)->toISOString(),
        ];
    }
}
