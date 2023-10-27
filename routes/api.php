<?php

use App\Http\Controllers\MollakController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\RegisterationController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Building\BuildingController;
use App\Http\Controllers\Building\FlatController;
use App\Http\Controllers\Community\CommentController;
use App\Http\Controllers\Community\PostController;
use App\Http\Controllers\Community\PostLikeController;
use App\Http\Controllers\Documents\DocumentsController;
use App\Http\Controllers\Facility\FacilityController;
use App\Http\Controllers\HelpDesk\ComplaintController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;

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

// Resident registeration
Route::post('/register', [RegisterationController::class, 'register']);
// Verify email
Route::post('/verify-otp', [VerificationController::class, 'verify']);

// Set password
Route::post('/set-password', [AuthController::class, 'setPassword']);

// These APIs work only if the user's account is active
Route::middleware(['active'])->group(function () {
    // Login routes for mobile app
    Route::post('/customer-login', [AuthController::class, 'customerLogin']);

    // Route for Refreshing the token
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // Forgot password route
    Route::post(
        '/forgot-password',
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

    // List all buildings for the logged in user
    Route::get('/user/flats', [UserController::class, 'fetchUserFlats']);
});
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
    // Get resident of a unit by mollak
    Route::get('/resident/{unitNumber}', [RegisterationController::class, 'fetchResidentDetails']);

    // Building API resource: Use only index method(To be changed a notmal route if we don't use other routes)
    Route::apiResource('buildings', BuildingController::class)->only(['index']);

    // Get all unit numbers(flats) for a given propertygroup(building)
    Route::get('/flats/{building}', [FlatController::class, 'fetchFlats']);

    // Resend otp
    Route::post('/resend-otp', [RegisterationController::class, 'resendOtp']);

    // List all tags
    Route::get('/tags', [TagController::class, 'index']);
});

/**
 * Community related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    //  List all posts for the buidling
    Route::get('/building/{building}/posts', [PostController::class, 'index']);

    // create a post
    Route::post('/building/{building}/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);

    // Post a comment on a post
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

    // Like and unlike a post
    Route::post('/posts/{post}/like', [PostLikeController::class, 'like'])->name('posts.like');
    Route::delete('/posts/{post}/unlike', [PostLikeController::class, 'unlike'])->name('posts.unlike');
    // List all users who liked the post
    Route::get('/posts/{post}/likers', [PostLikeController::class, 'likers'])->name('posts.likers');
});


/**
 * Facility related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    Route::get('buildings/{building}/facilities', [FacilityController::class, 'index']);

    // Book a facility
    Route::post('buildings/{building}/book/facility', [FacilityController::class, 'bookFacility'])->name('facility.book');


    // My bookings API - List all bookings for logged in user
    Route::get('building/{building}/user-bookings', [FacilityController::class, 'userBookings']);
});

/**
 * Help desk and happiness center related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    // Create complaint
    Route::post('building/{building}/complaints', [ComplaintController::class, 'create']);

    // List all complaints
    Route::get('/buildings/{building}/complaints', [ComplaintController::class, 'index']);

    // Complaint details API
    Route::get('complaints/{complaint}', [ComplaintController::class, 'show']);

    // Add a comment for a complaint
    Route::post('complaints/{complaint}/comments', [CommentController::class, 'addComment']);

    // List all comments for a given post
    Route::get('complaints/{complaint}/comments', [CommentController::class, 'listComplaintComments']);

    // mark a complaint as resolved
    Route::post('complaints/{complaint}/resolve', [ComplaintController::class, 'resolve']);
});

/**
 * Profile related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    // Details of user
    Route::get('/profile', [ProfileController::class, 'show']);

    // Update profile
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // Upload profile picture
    Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture']);

    // Change password
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    // Fetch all matching flats for the logged in user
    Route::get('/tenant/flats', [UserController::class, 'getUserFlats']);
});

/**
 * Services related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    Route::get('/buildings/{building}/services', [ServiceController::class, 'listServicesForBuilding']);
    Route::post('buildings/{building}/book/service', [ServiceController::class, 'bookService']);
});


/**
 * Documents related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    Route::get('/document-library', [DocumentsController::class, 'index']);
    Route::post('/document-upload', [DocumentsController::class, 'create']);
});
