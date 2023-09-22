<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\MollakController;
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

    Route::get('services-requests', [TestController::class, 'serviceRequest']);

    Route::get('service-parameters', [TestController::class, 'serviceParameters']);

    Route::post('upload-all', [TestController::class, 'uploadAll']);

    Route::get('oa-service-details/{oaService}', [TestController::class, 'getOaService']);
    // Get all propertirs
    Route::get('get-all-properties/{oa_id}', [MollakController::class, 'getProperties']);

    // Get service periods for a given property Id
    Route::get('get-service-periods/{propertyId}', [MollakController::class, 'getServicePeriod']);

    Route::get('mollak-sync-api', [MollakController::class, 'getData']);