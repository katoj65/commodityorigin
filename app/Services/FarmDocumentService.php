<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FarmDocumentService
{
    /**
     * Get every document uploaded against any farm, with its farm and
     * uploader, newest first.
     *
     * @return Collection<int, FarmDocument>
     */
    public function all(): Collection
    {
        return FarmDocument::query()->with(['farm', 'uploader'])->latest()->get();
    }

    /**
     * Store an uploaded document against a farm.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(Farm $farm, UploadedFile $file, array $data, int $userId): FarmDocument
    {
        $path = $file->store('farm-documents', 'public');

        return FarmDocument::query()->create([
            'farm_id' => $farm->id,
            'user_id' => $userId,
            'title' => $data['title'],
            'document_type' => $data['document_type'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Delete a farm document and its underlying file.
     */
    public function delete(FarmDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }
}
