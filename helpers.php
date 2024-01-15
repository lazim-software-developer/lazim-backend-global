<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\Pdf\Pdf as SpatiePdf;

function optimizeAndUpload($image, $path, $width = 474, $height = 622)
{
    $optimizedImage = Image::make($image)
        ->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->encode('jpg', 80); // 80 is the quality. You can adjust this value.

    $filename = uniqid() . '.' . $image->getClientOriginalExtension();
    $fullPath = $path . '/' . $filename;

    Storage::disk('s3')->put($fullPath, (string) $optimizedImage, 'public');

    return $fullPath;
}

function optimizeDocumentAndUpload($file, $path = 'dev', $width = 474, $height = 622)
{
    if ($file) {
        $extension = $file->getClientOriginalExtension();

        if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg' || $extension == 'JPG') {
            $optimizedImage = Image::make($file)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('jpg', 80);

            $filename = uniqid() . '.' . $extension;
            $fullPath = $path . '/' . $filename;

            Storage::disk('s3')->put($fullPath, (string) $optimizedImage, 'public');

            return $fullPath;
        } elseif ($extension == 'pdf') {
            $filename = uniqid() . '.' . $extension;
            $fullPath = $path . '/' . $filename;

            // Read the file's content
            $pdfContent = file_get_contents($file);

            // Store the file on S3
            Storage::disk('s3')->put($fullPath, $pdfContent, 'public');

            return $fullPath;
        } else {
            // Unsupported file type
            return response()->json(['error' => 'Unsupported file type.'], 422);
        }
    } else {
        // No file uploaded
        return response()->json(['error' => 'No file uploaded.'], 422);
    }
}
