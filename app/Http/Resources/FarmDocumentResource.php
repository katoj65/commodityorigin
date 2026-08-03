<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FarmDocumentResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'title' => $this->title,
            'document_type' => $this->document_type,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_url' => Storage::disk('public')->url($this->file_path),
            'uploaded_by' => $this->whenLoaded('uploader', fn (): ?string => $this->uploader?->name),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
        ];
    }
}
