<?php

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
