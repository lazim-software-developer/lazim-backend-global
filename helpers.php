<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\Pdf\Pdf as SpatiePdf;

function optimizeAndUpload($image, $path, $width = 474, $height = 622) {
    $optimizedImage = Image::make($image)
        ->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->encode('jpg', 80); // 80 is the quality. You can adjust this value.

    $filename = time() . '.' . $image->getClientOriginalExtension();
    $fullPath = $path . '/' . $filename;

    Storage::disk('s3')->put($fullPath, (string) $optimizedImage, 'public');

    return $fullPath;
}

function optimizeDocumentAndUpload($file, $path)
{
    $filename = time() . '.' . $file->getClientOriginalExtension();
    $fullPath = $path . '/' . $filename;

    Storage::disk('s3')->put($fullPath, 'public');

    return $fullPath;
}
