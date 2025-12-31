<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'url' => Storage::disk('public')->url($this->path),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
