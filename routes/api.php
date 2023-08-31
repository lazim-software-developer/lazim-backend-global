<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')
    ->get('/me', function (Request $request) {
        return $request->user();
    })
    ->name('api.user');

Route::name('api.')
    ->middleware('auth:sanctum')
    ->group(function () {});

Route::post('upload/e-services', [TestController::class, 'uploadEservices']);

Route::post('upload/happiness-center', [TestController::class, 'uploadhappinessCenter']);

Route::post('upload/accounts-payable', [TestController::class, 'uploadAccountsPayable']);

Route::post('upload/work-orders', [TestController::class, 'uploadWorkOrders']);

Route::post('upload/delinquents', [TestController::class, 'uploadDelinquents']);

Route::post('upload/balance-sheet', [TestController::class, 'uploadBalanceSheet']);

Route::post('upload/reserve-fund', [TestController::class, 'uploadReservedFund']);
