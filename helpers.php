<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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

    return Storage::disk('s3')->url($fullPath);
}
