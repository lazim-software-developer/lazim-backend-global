<?php

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Filament\Actions\Action;
use Spatie\Pdf\Pdf as SpatiePdf;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CustomResponseResource;

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

function imageUploadonS3($image, $path)
{
    $filename = uniqid() . '.' . $image->getClientOriginalExtension();
    $fullPath = $path . '/' . $filename;
    $file=Storage::disk('s3')->put($path, $image, 'public');
    return $file;
}

function optimizeDocumentAndUpload($file, $path = 'dev', $width = 474, $height = 622)
{
        if (!$file) {
            Log::error('##### Helper -> optimizeDocumentAndUpload ##### :- No file provided for upload', ['path' => $path, 'user_id' => auth()->id()]);
            return null;
        }
        $extension = $file->getClientOriginalExtension();
        $extension = strtolower($extension); // Normalize the extension to lowercase

        if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg') {
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
            \Illuminate\Support\Facades\Log::error('##### Helper -> optimizeDocumentAndUpload ##### :- Unsupported file type uploaded', ['file_type' => $extension, 'path' => $path, 'filename' => $file->getClientOriginalName(), 'user_id' => auth()->id()]);
            // return response()->json(['error' => 'Unsupported file type.'], 422);
            return null;
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
function NotificationTable($data){
    $notificationTypeId = DB::table('notification_types')->where('name', json_decode($data['custom_json_data'], true)['type'] ?? null)->value('id');
    if (!$notificationTypeId) {
        $notificationTypeId = DB::table('notification_types')->insertGetId([
            'name' => json_decode($data['custom_json_data'], true)['type'] ?? null,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    DB::table('notifications')->insert([
        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
        'type' => 'Filament\Notifications\DatabaseNotification',
        'notifiable_type' => $data['notifiable_type'] ?? null,
        'notifiable_id' =>$data['notifiable_id'] ?? null,
        'data' => json_encode([
            'actions' => [
                [
                    "name" => "view",
                    "iconPosition" => "before",
                    "label" => "View",
                    "size" => "sm",
                    "url" => $data['url'] ?? null,
                    "view" => "filament-actions::button-action",
                ],
            ],
            'body' => $data['body'] ?? null,
            'duration' => 'persistent',
            'icon' => 'heroicon-o-document-text',
            'iconColor' => 'warning',
            'title' => $data['title'] ?? null,
            'view' => 'notifications::notification',
            'viewData' => [
                'building_id' => $data['building_id'] ?? null,
            ],
            'format' => 'filament',
            'url' => $data['url'] ?? null,
        ]),
        'created_at' => now()->format('Y-m-d H:i:s'),
        'updated_at' => now()->format('Y-m-d H:i:s'),
        'custom_json_data' => $data['custom_json_data'] ?? null,
    ]);
}

if (! function_exists('backButton')) {
    /**
     * Generate a Filament back button action.
     *
     * @param string|null $url The URL to redirect to. Defaults to the dashboard.
     * @param string $label The button label. Defaults to 'Back'.
     * @param string $icon The button icon. Defaults to 'heroicon-o-arrow-left'.
     * @param string $color The button color. Defaults to 'gray'.
     * @return Action
     */
    function backButton(?string $url = null, string $label = 'Back', string $icon = 'heroicon-o-arrow-left', string $color = 'gray'): Action
    {
        return Action::make('back')
            ->label($label)
            ->icon($icon)
            ->url($url ?? \Filament\Facades\Filament::getPanel()->getPath())
            ->color($color);
    }
}

if (! function_exists('addTextToQR')) {
    /**
     * Generate a unique identifier.
     *
     * @return string
     */
    function addTextToQR($qrCode, $qrText = [], $qrCodeSize = 300, $width = 300, $height = 400): string
    {
        // Calculate dynamic font size (5% of QR size, clamped between 10 and 20)
        $fontSize = max(10, min(20, $qrCodeSize * 0.05));

        // Calculate text positioning
        $textY = $qrCodeSize + ($qrCodeSize * 0.1); // Start text 10% below QR code
        $lineSpacing = $fontSize * 1.5;

        // Create SVG text elements
        $svgText = '';
        foreach ($qrText as $line) {
            // Center text horizontally (approximate, as SVG text alignment is simpler)
            $svgText .= "<text x='50%' y='{$textY}' font-size='{$fontSize}' font-family='Arial, sans-serif' text-anchor='middle'>{$line}</text>";
            $textY += $lineSpacing;
        }

        // Append text to SVG
        $svg = str_replace('</svg>', $svgText . '</svg>', $qrCode);

        // Update SVG viewBox to accommodate text
        $height = $qrCodeSize + ($qrCodeSize * 0.1) + (count($qrText) * $lineSpacing);
        $svg = preg_replace(
            '/viewBox="0 0 (\d+) (\d+)"/',
            "viewBox='0 0 $qrCodeSize $height'",
            $svg
        );
        return $svg;
    }
}



if (! function_exists('convertSvgToPng')) {
    /**
     * Convert SVG to PNG.
     *
     * @param string $svgContent
     * @param string $output
     * @param int $size
     * @return string
     */
    function convertSvgToPng($svgContent, $outputPath, $size = 200)
    {
        // Sanitize SVG to prevent XSS
        $cleanSvgContent = preg_replace('/<\?xml[^>]*\?>\s*/i', '', $svgContent);

        if (!$cleanSvgContent) {
            throw new \Exception('Invalid SVG content');
        }

        // Create an image instance with Imagick
        $image = Image::make($cleanSvgContent);

        // Resize to ensure consistent output (optional)
        $image->resize($size, $size, function ($constraint) {
            $constraint->aspectRatio();
        });

        // Set the format to PNG
        $image->encode('png');

        // Save the PNG to the output path
        $image->save($outputPath);

        return $outputPath;
    }
}


if (! function_exists('fetchAndSaveInvoicePdf')) {
    /**
     * Fetch and save the invoice PDF from a given URL.
     *
     * @param string $url The URL to fetch the PDF from.
     * @param string $path The path to save the PDF.
     * @return string|null The path of the saved PDF or null if the fetch failed.
     */
    function fetchAndSaveInvoicePDF(string $url, string $path): ?string
    {
        try {
            $response = Http::withoutVerifying()->get($url);
            if ($response->successful()) {
                $filename = uniqid() . '.pdf';
                Storage::disk('s3')->put($path . '/' . $filename, $response->body(), 'public');
                return $path . '/' . $filename;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching invoice PDF', ['url' => $url, 'error' => $e->getMessage()]);
        }
        return null;
    }

}
