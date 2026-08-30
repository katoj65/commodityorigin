<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\LotImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LotImageService
{
    /**
     * A lot owner may keep at most this many gallery photos.
     */
    public const MAX_IMAGES = 3;

    /**
     * Store as many of the given files as fit within the per-lot cap,
     * appended after whatever images the lot already has.
     *
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, LotImage>
     */
    public function store(Lot $lot, array $files): Collection
    {
        $available = self::MAX_IMAGES - $lot->images()->count();
        $files = array_slice($files, 0, max(0, $available));

        $nextPosition = (int) ($lot->images()->max('position') ?? -1) + 1;

        return collect($files)->values()->map(function (UploadedFile $file, int $i) use ($lot, $nextPosition) {
            return LotImage::query()->create([
                'lot_id' => $lot->id,
                'image' => $file->store('lot-images', 'public'),
                'position' => $nextPosition + $i,
            ]);
        });
    }

    /**
     * Delete a lot's gallery photo and its underlying file.
     */
    public function delete(LotImage $image): void
    {
        Storage::disk('public')->delete($image->image);
        $image->delete();
    }
}
