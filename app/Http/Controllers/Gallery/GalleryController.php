<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryImageResource;
use App\Models\GalleryImage;
use App\Services\GalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __construct(private readonly GalleryService $gallery)
    {
    }

    /**
     * Display the gallery. Every authenticated user may view it; only
     * admins can upload, edit, or delete images.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Gallery/GalleryPage', [
            'images' => GalleryImageResource::collection($this->gallery->all())->resolve(),
            'canManage' => Gate::allows('create', GalleryImage::class),
        ]);
    }

    /**
     * Upload a new image. Admins only.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', GalleryImage::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $this->gallery->store($request->file('image'), $validated, $request->user()->id);

        return back()->with('success', 'Image uploaded successfully.');
    }

    /**
     * Update an image's title or description. Admins only.
     */
    public function update(Request $request, GalleryImage $galleryImage): RedirectResponse
    {
        Gate::authorize('update', $galleryImage);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->gallery->update($galleryImage, $validated);

        return back()->with('success', 'Image updated successfully.');
    }

    /**
     * Delete an image. Admins only.
     */
    public function destroy(GalleryImage $galleryImage): RedirectResponse
    {
        Gate::authorize('delete', $galleryImage);

        $this->gallery->delete($galleryImage);

        return back()->with('success', 'Image deleted successfully.');
    }
}
