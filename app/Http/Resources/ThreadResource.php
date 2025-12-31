<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'locale' => $this->locale,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'is_locked' => $this->is_locked,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'assigned_department' => new DepartmentResource($this->whenLoaded('assignedDepartment')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'last_reply_at' => $this->last_reply_at?->toIso8601String(),
            'last_user_reply_at' => $this->last_user_reply_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
