<?php

use App\Filament\Resources\LedgersResource\Pages\ListReceipts;
use App\Http\Controllers\OwnerAssociationReceipts;
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
use App\Http\Controllers\Vendor\MasterController;
use Filament\Pages\Page;

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
    return redirect('/admin/login');
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

// List all services for the budget
Route::get('/budget/{budget}/available-services/{subcategory}', [MasterController::class, 'getAvailableServices']);

Route::post('/get-delinquent-owners', [DelinquentController::class, 'getDelinquentOwners']);

Route::post('/get-general-fund',[GeneralFundController::class,'getGeneralFund']);

Route::post('/get-reserve-fund',[ReserveFundController::class,'getReserveFund']);

Route::get('/invoice',[OwnerAssociationInvoice::class,'invoice'])->name('invoice');

Route::get('/receipt',[OwnerAssociationReceipts::class,'receipt'])->name('receipt');




// Route::get('/admin/ledgers/{invoice}/receipts', function () {
//     return redirect()->to('/admin/ledgers/{invoice}/receipts');
// })->name('admin.ledgers.receipts');