<?php

use App\Http\Controllers\MollakController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterationController;

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

    // Get all property groups
    Route::get('/property-groups', [MollakController::class, 'fetchPropertyGroups']);

    // Get service periods for a given property Id
    Route::get('/service-periods/{propertyId}', [MollakController::class, 'fetchServicePeriods']);

});
Route::get('testing',[TestController::class,'test']);

/**
 * Middleware Group: API Token Protection
 *
 * This middleware group ensures that the API endpoints within are protected
 * using a custom API token mechanism. This allows for controlled access to these
 * endpoints without requiring user authentication via Sanctum.
 *
 * Note: These endpoints are designed to be accessed in scenarios like registration forms
 * where user authentication might not be available but controlled access is still required.
 */
Route::middleware(['api.token'])->group(function () {
    // Get all property groups
    Route::get('/property-groups/{oaId}', [MollakController::class, 'fetchPropertyGroups']);

    // Get all unit numbers(flats) for a given propertygroup(building)
    Route::get('/units/{propertyGroupId}', [MollakController::class, 'fetchUnits']);

    // Get resident of a unit by mollak
    Route::get('/resident/{unitNumber}', [RegisterationController::class, 'fetchResidentDetails']);
});


Route::get('/test', [RegisterationController::class, 'testAPI']);
