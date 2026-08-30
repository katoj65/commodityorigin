<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageUploadHelper
{
    /**
     * The maximum size, in kilobytes, accepted for any uploaded image
     * anywhere in the app. Kept well under typical PHP `post_max_size` /
     * `upload_max_filesize` defaults so oversized files fail Laravel's own
     * validation with a friendly message instead of the request being
     * rejected earlier by PHP with an unhandled PostTooLargeException.
     */
    public const MAX_KILOBYTES = 2048;

    /**
     * The only image formats accepted anywhere in the app.
     */
    public const MIME_TYPES = 'jpg,jpeg,png,webp';

    /**
     * Validation rules for a single image upload field.
     *
     * @return array<int, string>
     */
    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:'.self::MIME_TYPES,
            'max:'.self::MAX_KILOBYTES,
        ];
    }

    /**
     * Validation rules for one file within a multi-image upload field (e.g.
     * `images.*`) — the field itself is validated separately as an array.
     *
     * @return array<int, string>
     */
    public static function itemRules(): array
    {
        return [
            'image',
            'mimes:'.self::MIME_TYPES,
            'max:'.self::MAX_KILOBYTES,
        ];
    }

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

