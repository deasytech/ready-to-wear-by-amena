<?php

namespace App\Support;

class Media
{
    /**
     * Resolve a stored media path to a public URL. Passes external/placeholder
     * URLs (used by dev seeders) through untouched; local disk paths are
     * resolved via the public storage symlink.
     */
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : asset('storage/'.$path);
    }
}
