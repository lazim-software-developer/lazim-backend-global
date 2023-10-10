<?php

use App\Http\Controllers\MollakController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;

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

// OA Login
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// These APIs work only if the user's account is active
Route::middleware(['active'])->group(function () {
    // Login routes for mobile app
    Route::post('/customer-login', [AuthController::class, 'customerLogin']);

    // Route for Refreshing the token
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // Forgot password route
    Route::post('/forgot-password',
        [ResetPasswordController::class, 'forgotPassword']
    );
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
});


Route::middleware('auth:sanctum')
    ->get('/me', function (Request $request) {
        return $request->user();
    })
    ->name('api.user');

Route::group(['middleware' => ["auth:sanctum", "verified"]], function () {

    Route::get('services-requests', [TestController::class, 'serviceRequest']);

    Route::get('service-parameters', [TestController::class, 'serviceParameters']);
    
    Route::post('upload-all', [TestController::class, 'uploadAll']);
    
    Route::get('oa-service-details/{oaService}', [TestController::class, 'getOaService']);
    
    // Get all propertirs
    Route::get('get-all-properties/{oa_id}', [MollakController::class, 'getProperties']);
    
    // Get service periods for a given property Id
    Route::get('get-service-periods/{propertyId}', [MollakController::class, 'getServicePeriod']);

});
