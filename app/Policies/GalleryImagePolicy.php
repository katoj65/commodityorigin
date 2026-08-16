<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
{
    /**
     * Every authenticated user may browse the gallery.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Every authenticated user may view a single image.
     */
    public function view(User $user, GalleryImage $galleryImage): bool
    {
        return true;
    }

    /**
     * Only admins may upload images.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may edit an image's title or description.
     */
    public function update(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may delete an image.
     */
    public function delete(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin();
    }
}
