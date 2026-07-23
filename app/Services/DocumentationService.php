<?php

namespace App\Services;

use App\Models\Documentation;
use App\Models\DocumentationMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DocumentationService
{
    /**
     * Get a base query builder for documents.
     */
    public function query(): Builder
    {
        return Documentation::query();
    }

    /**
     * Get every document in the knowledge base, newest first — documentation
     * is shared across the whole platform, not scoped to one user.
     *
     * @return Collection<int, Documentation>
     */
    public function all(): Collection
    {
        return Documentation::query()->with('uploader')->latest()->get();
    }

    /**
     * Upload a new document.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Documentation
    {
        return Documentation::query()->create($data)->refresh();
    }

    /**
     * Update a document's title, description, or category.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Documentation $documentation, array $data): Documentation
    {
        $documentation->update($data);

        return $documentation;
    }

    /**
     * Delete a document, along with its stored file.
     */
    public function destroy(Documentation $documentation): void
    {
        Storage::disk('public')->delete($documentation->file_path);

        $documentation->delete();
    }

    /**
     * Get every active document category, in display order — this backs
     * the category picker documents are uploaded under.
     *
     * @return Collection<int, DocumentationMetadata>
     */
    public function categories(): Collection
    {
        return DocumentationMetadata::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get every document category regardless of active state, in display
     * order — for management/admin views.
     *
     * @return Collection<int, DocumentationMetadata>
     */
    public function allCategories(): Collection
    {
        return DocumentationMetadata::query()->orderBy('sort_order')->get();
    }

    /**
     * Create a new document category.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): DocumentationMetadata
    {
        return DocumentationMetadata::query()->create($data)->refresh();
    }

    /**
     * Update an existing document category.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(DocumentationMetadata $category, array $data): DocumentationMetadata
    {
        $category->update($data);

        return $category;
    }

    /**
     * Delete a document category.
     */
    public function destroyCategory(DocumentationMetadata $category): void
    {
        $category->delete();
    }
}
