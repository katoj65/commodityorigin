<?php

namespace App\Services;

use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GalleryService
{
    /**
     * Get every gallery image, newest first — the gallery is shared across
     * the whole platform, not scoped to one user.
     *
     * @return Collection<int, GalleryImage>
     */
    public function all(): Collection
    {
        return GalleryImage::query()->with('uploader')->latest()->get();
    }

    /**
     * Store an uploaded image.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(UploadedFile $file, array $data, int $userId): GalleryImage
    {
        $path = $file->store('gallery', 'public');

        return GalleryImage::query()->create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ])->refresh();
    }

    /**
     * Update an image's title or description.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(GalleryImage $galleryImage, array $data): GalleryImage
    {
        $galleryImage->update($data);

        return $galleryImage;
    }

    /**
     * Delete an image, along with its stored file.
     */
    public function delete(GalleryImage $galleryImage): void
    {
        Storage::disk('public')->delete($galleryImage->file_path);

        $galleryImage->delete();
    }
}
