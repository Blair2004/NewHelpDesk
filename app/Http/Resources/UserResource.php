<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'preferred_locale' => $this->preferred_locale,
            'is_staff' => $this->is_staff,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
