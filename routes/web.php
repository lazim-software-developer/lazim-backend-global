<?php

use Livewire\Livewire;
use App\Livewire\VendorRegistration;
use App\Filament\Pages\BudgetListing;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Filament\Pages\OAM\CreateTender;
use App\Http\Controllers\TestController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FlatImportController;
use App\Http\Controllers\Master\PDFController;
use App\Http\Controllers\GeneralFundController;
use App\Http\Controllers\ReserveFundController;
use App\Http\Controllers\BudgetImportController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\OwnerAssociationInvoice;
use App\Http\Controllers\Vendor\MasterController;
use App\Http\Controllers\BuildingImportController;
use App\Http\Controllers\OwnerAssociationReceipts;
use App\Http\Controllers\Vendor\DelinquentController;




use SimpleSoftwareIO\QrCode\Facades\QrCode;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::get('/ping', function () {
    dd("data");

})->name('api.ping');

 Route::get('/QR-test', function () {
    try {
        // Define QR code content
        $qrCodeContent = [
            'floors' => 1,
            'building_id' => 1,
            'code' => 'MX-1234',
        ];

        // Generate QR code
        $qrCodeSize = 1000; // Match the destination image width
        $width = 1000;
        $height = $qrCodeSize + 100; // Enough space for QR code and text
        $qrCode = QrCode::format('png')
            ->size($qrCodeSize)
            ->errorCorrection('H')
            ->margin(4)
            ->generate(json_encode($qrCodeContent));
        $qrText = "SU/GF/MX-1234";

        $qrImage = addTextToQR($qrCode, $qrText, $qrCodeSize, $width, $height);

        // // Create a blank image with white background
        // $image = imagecreatetruecolor($width, $height);
        // if (!$image) {
        //     throw new \Exception('Failed to create image with imagecreatetruecolor.');
        // }

        // // Load the QR code into a GD resource
        // $qrImage = imagecreatefromstring($qrCode);
        // if (!$qrImage) {
        //     throw new \Exception('Failed to create image from QR code string.');
        // }

        // // Allocate colors
        // $white = imagecolorallocate($image, 255, 255, 255);
        // $black = imagecolorallocate($image, 0, 0, 0);

        // // Fill the background
        // imagefill($image, 0, 0, $white);

        // // Copy QR code onto the image
        // imagecopy($image, $qrImage, 0, 0, 0, 0, $qrCodeSize, $qrCodeSize);

        // // Define font path
        // $fontPath = storage_path('app/fonts/arial.ttf');
        // if (!file_exists($fontPath)) {
        //     throw new \Exception('Font file not found at: ' . $fontPath);
        // }

        // // Add text below the QR code
        // $text = "MX-1234";
        // $fontSize = 16;
        // $textY = $qrCodeSize + 30; // Start text below QR code

        // // Split text into lines
        // $lines = explode("\n", $text);
        // foreach ($lines as $line) {
        //     $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
        //     if ($bbox === false) {
        //         throw new \Exception('Failed to calculate text bounding box for: ' . $line);
        //     }
        //     $textWidth = $bbox[2] - $bbox[0];
        //     $textX = ($width - $textWidth) / 2; // Center text
        //     if (!imagettftext($image, $fontSize, 0, $textX, $textY, $black, $fontPath, $line)) {
        //         throw new \Exception('Failed to render text: ' . $line);
        //     }
        //     $textY += 25; // Line spacing
        // }

        // // Output the image to a string
        // ob_start();
        // imagepng($image);
        // $imageData = ob_get_clean();
        // if (empty($imageData)) {
        //     throw new \Exception('Failed to generate PNG image data.');
        // }

        // // Clean up
        // imagedestroy($qrImage);
        // imagedestroy($image);

        // Output the image as base64 in an HTML img tag
        // return '<img src="data:image/png;base64,' . base64_encode($imageData) . '" alt="QR Code" />';
        return '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" />';
    } catch (\Exception $e) {
        // Output error message for debugging
        return 'Error: ' . $e->getMessage();
    }
});

// Route::get('/QR-test', function () {
//     // $text='test@example.com';
//     // $string = $text;
//     // $font   = 3;
//     // $width  = ImageFontWidth($font) * strlen($string);
//     // $height = ImageFontHeight($font);
//     // $im = @imagecreate ($width,$height);
//     // $background_color = imagecolorallocate ($im, 255, 255, 255); //white background
//     // $text_color = imagecolorallocate ($im, 0, 0,0);//black text
//     // imagestring ($im, $font, 0, 0, $string, $text_color);
//     // ob_start();
//     // imagepng($im);
//     // $imstr = base64_encode(ob_get_clean());
//     // imagedestroy($im);
//     // Generate QR code as PNG
//      $qrCodeContent = [
//             'floors' => 1,
//             'building_id' => 1,
//             'code'=>'MX-1234',
//         ];
//         $qrCodeSize = 500;
//         $width = 300;
//         $height = 400;
//         $qrCode = QrCode::format('png')->size(300)->errorCorrection('H')->margin(4)->generate(json_encode($qrCodeContent));

//         // Create a blank image with white background
//         $image = imagecreatetruecolor($width, $height);
//         // Load the QR code into a GD resource
//         $qrImage = imagecreatefromstring($qrCode);
//         // $imageWidth = imagesx($qrImage);
//         // $imageHeight = imagesy($qrImage);

//         // Allocate colors
//         $white = imagecolorallocate($image, 255, 255, 255);
//         $black = imagecolorallocate($image, 0, 0, 0);

//         // Fill the background
//         imagefill($image, 0, 0, $white);

//         // Copy QR code onto the image, centered
//         // imagecopy($image, $qrImage, ($width - $imageWidth) / 2, 50, 0, 0, $imageWidth, $imageHeight);
//         imagecopy($image, $qrImage, 0, 0, 0, 0, $qrCodeSize, $qrCodeSize);

//         // Define font path (adjust to your font file location)
//         $fontPath = storage_path('app/fonts/arial.ttf');

//         // Add text below the QR code
//         $text = "Building: 1 \nFloor: 1 \nLocation: MX-1234";
//         $fontSize = 16;
//         $textY = $qrCodeSize + 30; // Start text below QR code

//         // Split text into lines
//         $lines = explode("\n", $text);
//         foreach ($lines as $line) {
//             $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
//             $textWidth = $bbox[2] - $bbox[0];
//             $textX = ($width - $textWidth) / 2; // Center text
//             imagettftext($image, $fontSize, 0, $textX, $textY, $black, $fontPath, $line);
//             $textY += 25; // Line spacing
//         }

//         // Output the image to a string
//         ob_start();
//         imagepng($image);
//         $imageData = ob_get_clean();
//         // Clean up
//         imagedestroy($qrImage);
//         imagedestroy($image);

//         //  'data:image/png;base64,' . base64_encode($imageData);
//     echo '<img src="data:image/png;base64,' . base64_encode($imageData).'" alt="QR Code" />';
//     // return view('index',array('data'=>$imstr));
// dd("hello");
// });


Livewire::setUpdateRoute(function ($handle) {
    return Route::post('GIT/lazim-backend/public/livewire/update', $handle);
});
Route::get('/', function () {
    return redirect('/app/login');
});
// Route::get('/admin/custom-page', CustomPage::class)->name('filament.pages.custom-page');
// Route::get('/app', function () {
//     return redirect('/app/login');
// });

Route::middleware(['auth:sanctum', 'verified'])
    ->get('/dashboard', function () {
        return view('dashboard');
    })
    ->name('dashboard');

Route::prefix('/')
    ->middleware(['auth:sanctum', 'verified']);
Route::get('/vendors/create', VendorRegistration::class);

// Service chanrge
Route::get('service-charge/{saleNOC}/generate-pdf/', [PDFController::class, 'serviceChargePDF']);
// // ROutes for PDF links
// Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
// });

Route::get('/download/sample-budget-file', [BudgetImportController::class, 'downloadSampleBudgetFile'])
    ->name('download.sample-budget-file');

Route::post('/get-vendors-based-on-services', [MasterController::class, 'getVendorsBasedOnServices'])->name('vendors.based.on.services');

// Filament resources
Route::get('/filament/budget-listing/{building}', [BudgetListing::class, 'mount'])
    ->name('filament.pages.budget-listing');

Route::post('admin/{budget}/tender/create', [CreateTender::class, 'store'])->name('tender.create');
Route::post('app/{budget}/tender/create', [CreateTender::class, 'store'])->name('tenders.create');

// List all services for the budget
Route::get('/budget/{budget}/available-services/{subcategory}', [MasterController::class, 'getAvailableServices']);

Route::post('/get-delinquent-owners', [DelinquentController::class, 'getDelinquentOwners']);

Route::post('/get-general-fund', [GeneralFundController::class, 'getGeneralFund']);

Route::post('/get-reserve-fund', [ReserveFundController::class, 'getReserveFund']);

Route::post('/get-general-fund-mollak', [GeneralFundController::class, 'getGeneralFundMollak']);

Route::post('/get-reserve-fund-mollak', [ReserveFundController::class, 'getReserveFundMollak']);

Route::post('/get-trial-balance', [TrialBalanceController::class, 'getTrialBalance']);

Route::get('/invoice', [OwnerAssociationInvoice::class, 'invoice'])->name('invoice');

Route::get('/receipt', [OwnerAssociationReceipts::class, 'receipt'])->name('receipt');

Route::get('/download/sample-building-file', [BuildingImportController::class, 'downloadSampleFile'])
    ->name('download.sample-building-file');
Route::get('/download/import-report/{filename}', [BuildingImportController::class, 'downloadReport'])
    ->name('download.import.report')
    ->where('filename', '.*');
Route::get('/download/sample-flat-file', [FlatImportController::class, 'downloadSampleFile'])
    ->name('download.sample-flat-file');

// Route::get('/test',[PDFController::class,'qrCode']);
Route::get('/qr_code', function () {
    $data = Session::get('data');
    // Now you can use $data in your view or wherever you need it
    return view('pdf.qr-code', ['data' => $data]);
});

Route::post('/download', [TestController::class, 'download'])->name('download');

Route::post('/upload', [TestController::class, 'uploadAll'])->name('uploadAll');
// Route::get('/admin/ledgers/{invoice}/receipts', function () {
//     return redirect()->to('/admin/ledgers/{invoice}/receipts');
// })->name('admin.ledgers.receipts');

Route::get('/filament/custom/asset-fetch-data', function () {
    $data = Session::get('data');
    return view('filament.custom.asset-fetch-data', ['data' => $data]);
})->name('asset-fetch-data');

Route::get('/redirect-os', [TestController::class, 'redirectBasedOnOS'])->name('redirect.os');

Route::post('/qr/feedback', [FeedbackController::class, 'submitFeedback'])->name('qr.feedback.submit');
Route::get('/qr/feedback', [FeedbackController::class, 'index'])->name('qr.feedback.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/app/edit-invoice-status/{record}', \App\Filament\Resources\OwnerAssociationInvoiceResource\Pages\EditInvoiceStatus::class)
        ->middleware('role:Property Manager') // Add role middleware to ensure only Property Managers can access
        ->name('edit-invoice-status');

    // Route::get('/app/edit-receipt-status/{record}', \App\Filament\Resources\OwnerAssociationReceiptResource\Pages\EditStatus::class)
    // ->middleware('role:Property Manager')
    // ->name('edit-receipt-status');


});
