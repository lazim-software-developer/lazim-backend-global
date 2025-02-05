<?php

use App\Http\Resources\CustomResponseResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\Pdf\Pdf as SpatiePdf;
use Stripe\PaymentIntent;
use Stripe\Stripe;

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
        Log::info($extension);

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
        } elseif (in_array($extension, ['pdf', 'doc', 'docx'])) {
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

function createPaymentIntent($amount, $email) {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    try {
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'aed',
            'receipt_email' => $email,
        ]);

        return $paymentIntent;
    } catch (\Exception $e) {
        return (new CustomResponseResource([
            'title' => 'Error',
            'message' => $e->getMessage(),
            'code' => 500,
        ]))->response()->setStatusCode(500);
    }
}

if (!function_exists('generate_ticket_number')) {
    function generate_ticket_number($type)
    {
        return $type . date("d") . "-" . strtoupper(bin2hex(random_bytes(2))) . "-" . date("hi");
    }
}

function generateAlphanumericOTP($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $otp;
}
