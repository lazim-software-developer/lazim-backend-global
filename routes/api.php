<?php

use App\Http\Controllers\MollakController;
use App\Http\Controllers\Technician\TechnicianController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Vendor\ContractController;
use App\Http\Controllers\Vendor\DocumentsUploadController;
use App\Http\Controllers\Vendor\EscalationMatrixController;
use App\Http\Controllers\Vendor\SelectServicesController;
use App\Http\Controllers\Vendor\SnagDashboardController;
use App\Http\Controllers\Vendor\VendorComplaintController;
use App\Http\Controllers\Vendor\VendorRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\AppFeedbackController;
use App\Http\Controllers\Building\BuildingController;
use App\Http\Controllers\Building\FlatController;
use App\Http\Controllers\Community\CommentController;
use App\Http\Controllers\Community\PostController;
use App\Http\Controllers\Community\PostLikeController;
use App\Http\Controllers\Documents\DocumentsController;
use App\Http\Controllers\Facility\FacilityController;
use App\Http\Controllers\Forms\AccessCardController;
use App\Http\Controllers\Forms\FitOutFormsController;
use App\Http\Controllers\Forms\MoveInOutController;
use App\Http\Controllers\Forms\GuestController;
use App\Http\Controllers\Forms\ResidentialFormController;
use App\Http\Controllers\Forms\SaleNocController;
use App\Http\Controllers\HelpDesk\ComplaintController;
use App\Http\Controllers\Security\SecurityController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Technician\BuildingController as TechnicianBuildingController;
use App\Http\Controllers\Technician\TasksController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\WDAController;
use App\Http\Controllers\Vendor\VendorBuildingController;

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

// Resident registeration with email and phone
Route::post('/register', [RegistrationController::class, 'registerWithEmailPhone']);
// Resident registeration with Passport/Emirates id
Route::post('/register-with-passport', [RegistrationController::class, 'registerWithEmiratesOrPassport']);

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
    Route::get('/resident/{unitNumber}', [RegistrationController::class, 'fetchResidentDetails']);

    // Building API resource: Use only index method(To be changed a notmal route if we don't use other routes)
    Route::apiResource('buildings', BuildingController::class)->only(['index']);

    // Get all unit numbers(flats) for a given propertygroup(building)
    Route::get('/flats/{building}', [FlatController::class, 'fetchFlats']);

    // Resend otp
    Route::post('/resend-otp', [RegistrationController::class, 'resendOtp']);

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

    // Check if post upload is enabled for a building
    Route::get('/buildings/{building}/post-upload-permission', [PostController::class, 'checkPostUploadPermission']);
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

    //Complaint Update API
    Route::patch('complaints/{complaint}/update', [ComplaintController::class,'update']);

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

    // List all family members of logged in user
    Route::get('/family-members/{building}', [UserController::class, 'getFamilyMembers']);
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
    Route::get('/documents', [DocumentsController::class, 'index']);
    Route::post('/document-upload', [DocumentsController::class, 'create']);
    Route::get('/fetch-other-documents', [DocumentsController::class, 'fetchOtherDocuments']);

    // List all Owners for a given flat
    Route::get('/flat/{flat}/owners', [FlatController::class, 'fetchFlatOwners']);
});

/**
 * Forms related APIs
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->prefix('forms')->group(function () {
    Route::post('/move-in-out', [MoveInOutController::class, 'store']);
    Route::post('/guest-registration', [GuestController::class, 'store']);
    Route::post('/sale-noc', [SaleNocController::class, 'store']);
    Route::post('/fit-out', [FitOutFormsController::class, 'store']);
    Route::post('/residential', [ResidentialFormController::class, 'store']);
    Route::post('/access-card', [AccessCardController::class, 'create']);

    // View form status
    Route::get('/status/{building}', [AccessCardController::class, 'fetchFormStatus']);
    Route::get('/sale-noc/{saleNoc}/status', [SaleNocController::class, 'fetchNocFormStatus']);
    Route::post('/sale-noc/{saleNoc}/upload-document', [SaleNocController::class, 'uploadDocument']);

    // Upload document to S3 - For NOC Page
    Route::post('/upload-document', [SaleNocController::class, 'uploadNOCDocument']);
});

// API  to fetch Security for a building
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->prefix('building')->group(function () {
    Route::get('/{building}/security', [SecurityController::class, 'fetchSecurity']);
});

// App suggestion and feedback
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    Route::post('/feedback', [AppFeedbackController::class, 'store']);
});

// API for master list
Route::middleware(['api.token'])->group(function () {
    Route::get('/sub-categories/{subcategory}/services', [SelectServicesController::class, 'listServices']);
    Route::get('/sub-categories',[SelectServicesController::class, 'listSubCategories']);
});

// Vendor APIs
Route::middleware(['api.token'])->prefix('vendor')->group(function () {
    Route::post('/registration', [VendorRegistrationController::class, 'registration']);
    Route::post('/company-detail', [VendorRegistrationController::class, 'companyDetails']);
    Route::post('/managers/{vendor}', [VendorRegistrationController::class, 'managerDetails']);
    // Add a new custom service and attch to vendor
    Route::post('/add-service/{vendor}', [SelectServicesController::class, 'addService']);
    // Attcah existing service to vendor
    Route::post('/{vendor}/tag-services', [SelectServicesController::class, 'tagServices']);
    Route::post('/{vendor}/documents-upload', [DocumentsUploadController::class, 'documentsUpload']);
});

// Vendor APIs after logging in
Route::middleware(['auth:sanctum', 'active'])->prefix('vendor')->group(function () {
    // List vendor details of logged in user
    Route::get('/details', [VendorRegistrationController::class, 'showVendorDetails']);
    Route::get('/{vendor}/view-manager', [VendorRegistrationController::class, 'showManagerDetails']);
    Route::get('/{vendor}/services', [SelectServicesController::class, 'showServices']);
    Route::get('/{vendor}/show-documents', [DocumentsUploadController::class, 'showDocuments']);
    Route::post('/{vendor}/escalation-matrix', [EscalationMatrixController::class, 'store']);
    Route::get('/{vendor}/escalation-matrix', [EscalationMatrixController::class, 'show']);
    Route::post('/escalation-matrix/{escalationmatrix}/delete', [EscalationMatrixController::class, 'delete']);
    Route::get('/{vendor}/tickets',[VendorComplaintController::class, 'listComplaints']);
    Route::post('/vendor-comment/{complaint}',[VendorComplaintController::class, 'addComment']);
    Route::get('/list-buildings/{vendor}',[VendorBuildingController::class,'listBuildings']);
    Route::get('/{vendor}/contracts',[ContractController::class,'index']);

    //Dashboard Snags
    Route::get('/dashboard-snag-stats/{vendor}',[SnagDashboardController::class,'tasks']);

    // Invoice create API
    Route::post('/invoice',[InvoiceController::class, 'store']);

    // WDA create API
    Route::post('/wda', [WDAController::class, 'store']);

    // List all WDAs
    Route::get('/{vendor}/wda', [WDAController::class, 'index']);

    // List all invoices 
    Route::get('/{vendor}/invocies', [InvoiceController::class, 'index']);

    // invoice dashboard
    Route::get('/dashboard-invoice-stats/{vendor}',[InvoiceController::class,'stats']);
});

// Technician Related APIs
Route::middleware(['auth:sanctum', 'active'])->prefix('technician')->group(function () {
    // Registration
    Route::post('/registration', [TechnicianController::class, 'registration']);
    // List all buildings for logged in technician
    Route::get('/buildings', [TechnicianBuildingController::class, 'index']);
    Route::get('/tasks', [TasksController::class, 'index']);
    //List all technicians for a service
    Route::get('/{service}/technicians',[TechnicianController::class, 'index']);
    Route::patch('/active-deactive/{technician}',[TechnicianController::class, 'activeDeactive']);
    Route::post('/attach-technician/{technician}',[TechnicianController::class, 'attachTechnician']);
    Route::post('/assign-technician/{complaint}',[TechnicianController::class, 'assignTechnician']);
});
