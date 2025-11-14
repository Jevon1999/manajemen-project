<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->user_id,
            'username' => $this->username,
            'email' => $this->email,
            'full_name' => $this->full_name ?? $this->name,
            'role' => $this->role,
            'specialty' => $this->specialty,
            'status' => $this->status,
            'avatar' => $this->avatar,
        ];
    }
}
