<?php

namespace App\Services;

use App\Models\AgriculturalInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgriculturalInputService
{
    public const CATEGORIES = ['medicine', 'fertilizer'];

    /**
     * Get a base query builder for the store, newest first.
     */
    public function query(): Builder
    {
        return AgriculturalInput::query()->with('creator')->latest();
    }

    /**
     * Paginate the store listing, optionally filtered by search term and
     * category.
     */
    public function paginateForList(?string $search, ?string $category, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('tag', 'like', "%{$term}%")
                    ->orWhere('manufacturer', 'like', "%{$term}%"),
            ))
            ->when($category, fn (Builder $query, string $value) => $query->where('category', $value))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Aggregate stats for the store header.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return [
            'total_items' => AgriculturalInput::query()->count(),
            'medicine_count' => AgriculturalInput::query()->where('category', 'medicine')->count(),
            'fertilizer_count' => AgriculturalInput::query()->where('category', 'fertilizer')->count(),
            'out_of_stock_count' => AgriculturalInput::query()->where('stock_quantity', 0)->count(),
        ];
    }

    /**
     * Distinct tags currently in use, for a filter dropdown.
     *
     * @return Collection<int, string>
     */
    public function tagOptions(): Collection
    {
        return AgriculturalInput::query()
            ->whereNotNull('tag')
            ->distinct()
            ->orderBy('tag')
            ->pluck('tag');
    }

    /**
     * Add a new input to the store.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, int $userId, ?UploadedFile $image = null): AgriculturalInput
    {
        $imagePath = $image ? $image->store('agricultural-inputs', 'public') : null;

        return AgriculturalInput::query()->create([
            ...$data,
            'user_id' => $userId,
            'image' => $imagePath,
            'sku' => $this->generateSku($data['category']),
        ])->refresh();
    }

    /**
     * Update an existing input's listing.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(AgriculturalInput $agriculturalInput, array $data, ?UploadedFile $image = null): AgriculturalInput
    {
        if ($image) {
            if ($agriculturalInput->image) {
                Storage::disk('public')->delete($agriculturalInput->image);
            }

            $data['image'] = $image->store('agricultural-inputs', 'public');
        }

        $agriculturalInput->update($data);

        return $agriculturalInput;
    }

    /**
     * Remove an input from the store, along with its stored image.
     */
    public function delete(AgriculturalInput $agriculturalInput): void
    {
        if ($agriculturalInput->image) {
            Storage::disk('public')->delete($agriculturalInput->image);
        }

        $agriculturalInput->delete();
    }

    /**
     * Generate a unique, human-readable SKU for a new input.
     */
    protected function generateSku(string $category): string
    {
        $prefix = strtoupper(substr($category, 0, 3));

        do {
            $sku = "{$prefix}-".strtoupper(Str::random(6));
        } while (AgriculturalInput::query()->where('sku', $sku)->exists());

        return $sku;
    }
}
