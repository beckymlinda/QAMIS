<?php

namespace App\Support;

class ReportLogo
{
    public static function path(): ?string
    {
        foreach (['logo.png', 'logo1.png'] as $filename) {
            $path = public_path('images/'.$filename);
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function pdfSupported(): bool
    {
        return function_exists('imagecreatefrompng');
    }

    public static function dataUri(): ?string
    {
        if (! self::pdfSupported()) {
            return null;
        }

        $path = self::path();
        if (! $path) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }
}
