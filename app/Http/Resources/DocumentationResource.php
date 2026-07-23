<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'uploader_name' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'original_name' => $this->original_name,
            'file_url' => Storage::disk('public')->url($this->file_path),
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
