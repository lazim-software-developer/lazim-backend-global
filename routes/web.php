<?php

use App\Filament\Resources\LedgersResource\Pages\ListReceipts;
use App\Http\Controllers\OwnerAssociationReceipts;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\Vendor\DelinquentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Master\PDFController;
use App\Models\Vendor\Vendor;
use App\Livewire\VendorRegistration;
use App\Filament\Pages\BudgetListing;
use App\Filament\Pages\OAM\CreateTender;
use App\Http\Controllers\GeneralFundController;
use App\Http\Controllers\OwnerAssociationInvoice;
use App\Http\Controllers\ReserveFundController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Vendor\MasterController;
use App\Models\Master\Role;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

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

Route::get('/', function () {
    return redirect('/admin');
});

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

Route::post('/get-vendors-based-on-services', [MasterController::class, 'getVendorsBasedOnServices'])->name('vendors.based.on.services');

// Filament resources
Route::get('/filament/budget-listing/{building}', [BudgetListing::class, 'mount'])
    ->name('filament.pages.budget-listing');

Route::post('admin/{budget}/tender/create', [CreateTender::class, 'store'])->name('tender.create');
Route::post('app/{budget}/tender/create', [CreateTender::class, 'store'])->name('tenders.create');

// List all services for the budget
Route::get('/budget/{budget}/available-services/{subcategory}', [MasterController::class, 'getAvailableServices']);

Route::post('/get-delinquent-owners', [DelinquentController::class, 'getDelinquentOwners']);

Route::post('/get-general-fund',[GeneralFundController::class,'getGeneralFund']);

Route::post('/get-reserve-fund',[ReserveFundController::class,'getReserveFund']);

Route::post('/get-general-fund-mollak',[GeneralFundController::class,'getGeneralFundMollak']);

Route::post('/get-reserve-fund-mollak',[ReserveFundController::class,'getReserveFundMollak']);

Route::post('/get-trial-balance',[TrialBalanceController::class,'getTrialBalance']);

Route::get('/invoice',[OwnerAssociationInvoice::class,'invoice'])->name('invoice');

Route::get('/receipt',[OwnerAssociationReceipts::class,'receipt'])->name('receipt');

// Route::get('/test',[PDFController::class,'qrCode']);
Route::get('/qr_code',function(){
    $data = Session::get('data');
    // Now you can use $data in your view or wherever you need it
    return view('pdf.qr-code', ['data' => $data]);
});

Route::post('/download', [TestController::class, 'download'])->name('download');

Route::post('/upload',[TestController::class, 'uploadAll'])->name('uploadAll');
// Route::get('/admin/ledgers/{invoice}/receipts', function () {
//     return redirect()->to('/admin/ledgers/{invoice}/receipts');
// })->name('admin.ledgers.receipts');

Route::get('/filament/custom/asset-fetch-data', function () {
    $data = Session::get('data');
    return view('filament.custom.asset-fetch-data', ['data' => $data]);
})->name('asset-fetch-data');

Route::get('/redirect-os', [TestController::class, 'redirectBasedOnOS'])->name('redirect.os');
