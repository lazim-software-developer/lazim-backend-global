<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Master\PDFController;
use App\Models\Vendor\Vendor;
use App\Livewire\VendorRegistration;

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


// ROutes for PDF links
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    // Service chanrge 
    Route::get('service-charge/{flat}/generate-pdf/', [PDFController::class, 'serviceChargePDF']);
});