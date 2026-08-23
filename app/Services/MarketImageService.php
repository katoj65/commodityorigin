<?php

namespace App\Services;

use App\Models\Market;
use App\Models\MarketImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MarketImageService
{
    /**
     * A listing owner may keep at most this many gallery photos.
     */
    public const MAX_IMAGES = 3;

    /**
     * Store as many of the given files as fit within the per-listing cap,
     * appended after whatever images the listing already has.
     *
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, MarketImage>
     */
    public function store(Market $market, array $files): Collection
    {
        $available = self::MAX_IMAGES - $market->images()->count();
        $files = array_slice($files, 0, max(0, $available));

        $nextPosition = (int) ($market->images()->max('position') ?? -1) + 1;

        return collect($files)->values()->map(function (UploadedFile $file, int $i) use ($market, $nextPosition) {
            return MarketImage::query()->create([
                'market_id' => $market->id,
                'image' => $file->store('market-images', 'public'),
                'position' => $nextPosition + $i,
            ]);
        });
    }

    /**
     * Delete a listing's gallery photo and its underlying file.
     */
    public function delete(MarketImage $image): void
    {
        Storage::disk('public')->delete($image->image);
        $image->delete();
    }
}
