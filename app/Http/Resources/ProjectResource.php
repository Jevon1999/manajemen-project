<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->project_id,
            'name' => $this->project_name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'deadline' => optional($this->deadline)->toDateString(),
            'completion_percentage' => $this->completion_percentage,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'leader_id' => $this->leader_id,
            'leader' => $this->when(
                $this->relationLoaded('leader'),
                function() {
                    return $this->leader ? new UserResource($this->leader) : null;
                }
            ),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'members' => $this->when(
                $this->relationLoaded('members'),
                ProjectMemberResource::collection($this->members)
            ),
        ];
    }
}
