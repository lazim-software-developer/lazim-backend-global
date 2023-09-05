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
    ->group(function () {
    });
    Route::post('upload/1', [TestController::class, 'uploadEservices']);

    Route::post('upload/2', [TestController::class, 'uploadhappinessCenter']);

    Route::post('upload/3', [TestController::class, 'uploadBalanceSheet']);

    Route::post('upload/4', [TestController::class, 'uploadGeneralFund']);

    Route::post('upload/5', [TestController::class, 'uploadReservedFund']);

    Route::post('upload/6', [TestController::class, 'uploadBudgetVsActual']);

    Route::post('upload/7', [TestController::class, 'uploadAccountsPayable']);

    Route::post('upload/8', [TestController::class, 'uploadDelinquents']);

    Route::post('upload/9', [TestController::class, 'uploadCollection']);

    Route::post('upload/12', [TestController::class, 'uploadWorkOrders']);

    Route::get('services-requests', [TestController::class, 'serviceRequest']);

    Route::get('service-parameters', [TestController::class, 'serviceParameters']);

    Route::post('upload-all', [TestController::class, 'uploadAll']);

    Route::get('oa-service-details/{oaService}', [TestController::class, 'getOaService']);
