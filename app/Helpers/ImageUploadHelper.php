<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageUploadHelper
{
    /**
     * Store an uploaded image and return its storage path.
     */
    public static function store(?UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, $disk);
    }
}

