<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\Assets\PPMController;
use App\Http\Controllers\Community\CommunityController;
use App\Http\Controllers\FlatVisitorController;
use App\Http\Controllers\MollakController;
use App\Http\Controllers\Technician\TechnicianController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Vendor\ContractController;
use App\Http\Controllers\Vendor\DocumentsUploadController;
use App\Http\Controllers\Vendor\EscalationMatrixController;
use App\Http\Controllers\Vendor\ProposalController;
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
use App\Http\Controllers\Api\Tally\TallyIntigrationController;
use App\Http\Controllers\AppFeedbackController;
use App\Http\Controllers\Assets\AssetController;
use App\Http\Controllers\Building\BuildingController;
use App\Http\Controllers\Building\FlatController;
use App\Http\Controllers\Community\CommentController;
use App\Http\Controllers\Community\PollController;
use App\Http\Controllers\Community\PostController;
use App\Http\Controllers\Community\PostLikeController;
use App\Http\Controllers\Documents\DocumentsController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\Facility\FacilityController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\Forms\AccessCardController;
use App\Http\Controllers\Forms\FitOutFormsController;
use App\Http\Controllers\Forms\MoveInOutController;
use App\Http\Controllers\Forms\GuestController;
use App\Http\Controllers\Forms\ResidentialFormController;
use App\Http\Controllers\Forms\SaleNocController;
use App\Http\Controllers\Gatekeeper\ComplaintController as GatekeeperComplaintController;
use App\Http\Controllers\Gatekeeper\PatrollingController;
use App\Http\Controllers\HelpDesk\ComplaintController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Security\SecurityController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Technician\BuildingController as TechnicianBuildingController;
use App\Http\Controllers\Technician\TasksController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\TenderController;
use App\Http\Controllers\Vendor\WDAController;
use App\Http\Controllers\Vendor\VendorBuildingController;
use App\Http\Controllers\Gatekeeper\TenantsController;
use App\Http\Controllers\SubContractorsController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Vendor\ItemsController;
use App\Http\Controllers\Vendor\TLController;

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
Route::post('/register-with-document', [RegistrationController::class, 'registerWithDocument']);
Route::post('/documents-reupload/{resident}',[RegistrationController::class, 'reuploadDocument']);
Route::get('/documents-status/{resident}',[RegistrationController::class, 'documentStatus']);
Route::get('/view-documents/{resident}',[RegistrationController::class, 'viewDocuments']);
// owner list
Route::get('/owner-list/{flat}', [RegistrationController::class,'ownerList']);
Route::get('/all-owners', [RegistrationController::class, 'allOwners']);
//owner details
Route::get('/owner-details/{owner}', [RegistrationController::class,'ownerDetails']);

// Verify email
Route::post('/verify-otp', [VerificationController::class, 'verify']);

// Set password
Route::post('/set-password', [AuthController::class, 'setPassword']);

//expo//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
    Route::post('/expo', [AuthController::class, 'expo']);
    Route::get('/app-notification', [NotificationController::class, 'index']);
    Route::get('/clear-notifications', [NotificationController::class, 'clearNotifications']);
});


// These APIs work only if the user's account is active
Route::middleware(['active'])->group(function () {
    // Login routes for mobile app
    Route::post('/customer-login', [AuthController::class, 'customerLogin']);

    // Vendor login
    Route::post('/vendor-login', [AuthController::class, 'vendorLogin']);

    // Route for Refreshing the token
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // Security login
    Route::post('/gatekeeper-login', [AuthController::class, 'gateKeeperLogin']);

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
// API for master list 'api.token' middleware
Route::middleware([])->group(function () {
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
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
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

    // Check if inhouse services are enabled for a building
    Route::get('/buildings/{building}/show-inhouse-services', [ServiceController::class, 'checkInhouseServicePermission']);

    // Polls for a building
    //  List all polls for the buidling
    Route::get('/building/{building}/polls', [PollController::class, 'index']);
    // Route::get('/poll/{poll}', [PollController::class, 'show']);

    // create a post
    Route::post('/poll/{poll}', [PollController::class, 'store']);
});


/**
 * Facility related APIs
 */
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
    Route::get('buildings/{building}/facilities', [FacilityController::class, 'index']);

    // Book a facility
    Route::post('buildings/{building}/book/facility', [FacilityController::class, 'bookFacility'])->name('facility.book');

    // My bookings API - List all bookings for logged in user
    Route::get('building/{building}/user-bookings', [FacilityController::class, 'userBookings']);
});

/**
 * Help desk and happiness center related APIs
 */
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
    // Create complaint
    Route::post('building/{building}/complaints', [ComplaintController::class, 'create']);

    // CreateIncidents
    Route::post('building/{building}/incidents', [ComplaintController::class,'createIncident']);

    // List all complaints
    Route::get('/buildings/{building}/complaints', [ComplaintController::class, 'index']);

    // Complaint details API
    Route::get('complaints/{complaint}', [ComplaintController::class, 'show']);

    //Complaint Update API
    Route::patch('complaints/{complaint}/update', [ComplaintController::class, 'update']);

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
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
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

    //user delete
    Route::patch('/user-delete', [UserController::class, 'deleteUser']);
});

/**
 * Services related APIs
 */
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
    Route::get('/buildings/{building}/services', [ServiceController::class, 'listServicesForBuilding']);
    Route::post('buildings/{building}/book/service', [ServiceController::class, 'bookService']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles', [VehicleController::class, 'index']);

    // family members
    Route::post('/family-members/{building}',[FamilyMemberController::class, 'store']);
    Route::get('/fetch-family-members/{building}/{unit?}',[FamilyMemberController::class, 'index']);
    Route::delete('/delete-family-members/{familyMember}',[FamilyMemberController::class, 'delete']);
    Route::patch('/update-family-members/{familyMember}',[FamilyMemberController::class, 'update']);
    Route::get('/show-family-members/{familyMember}',[FamilyMemberController::class, 'show']);
});


/**
 * Payment APIs for Owners
 */
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->prefix('payments')->group(function () {
    Route::get('/{flat}/service-charges', [PaymentController::class, 'fetchServiceCharges']);

    // Access PDF link for serviceCharge
    Route::get('/{invoice}/pdf-link', [PaymentController::class, 'fetchPDF']);
    Route::get('/{invoice}/service-charge-pdf', [PaymentController::class, 'fetchServiceChargePDF']);

    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);

    Route::post('/{order}/payment-status', [PaymentController::class, 'fetchPaymentStatus']);

    Route::get('/{flat}/invoice-balance',[PaymentController::class,'fecthInvoiceDetails']);
});

/**
 * Documents related APIs
 */
//, 'phone.verified'
Route::middleware(['auth:sanctum', 'email.verified', 'active'])->group(function () {
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

    // Rejected APIs
    Route::get('/move-in/{movein}', [MoveInOutController::class, 'index']);

    // Update API for move in and move out
    Route::post('/move-in/{movein}/update', [MoveInOutController::class, 'update']);

    //Fit Out rejected API
    Route::get('/fit-out/status/{fitout}',[FitOutFormsController::class, 'index']);
});
//Contractor Request
Route::post('/fit-out/contractor/{fitout}',[FitOutFormsController::class, 'contractorRequest']);

// API  to fetch Security for a building
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->prefix('building')->group(function () {
    Route::get('/{building}/security', [SecurityController::class, 'fetchSecurity']);
});

// App suggestion and feedback
Route::middleware(['auth:sanctum', 'email.verified', 'phone.verified', 'active'])->group(function () {
    Route::post('/feedback', [AppFeedbackController::class, 'store']);
});

// API for master list 'api.token' middleware
Route::middleware([])->group(function () {
    Route::get('/sub-categories/{subcategory}/services', [SelectServicesController::class, 'listServices']);
    Route::get('/sub-categories', [SelectServicesController::class, 'listSubCategories']);
    Route::get('/categories', [SelectServicesController::class, 'listCategories']);
});

// Vendor APIs 'api.token' middleware
Route::middleware([])->prefix('vendor')->group(function () {
    Route::post('/registration', [VendorRegistrationController::class, 'registration']);
    Route::post('/company-detail', [VendorRegistrationController::class, 'companyDetails']);
    Route::post('/managers/{vendor}', [VendorRegistrationController::class, 'managerDetails']);
    // Add a new custom service and attch to vendor
    Route::post('/add-service/{vendor}', [SelectServicesController::class, 'addService']);
    // Attcah existing service to vendor
    Route::post('/{vendor}/tag-services', [SelectServicesController::class, 'tagServices']);
    Route::post('/{vendor}/untag-services', [SelectServicesController::class, 'untagServices']);
    Route::post('/{vendor}/documents-upload', [DocumentsUploadController::class, 'documentsUpload']);
    Route::get('/{vendor}/list-documents', [DocumentsUploadController::class, 'listDocuments']);
    Route::get('/owner-associations', [VendorRegistrationController::class, 'listOa']);
});

// Vendor APIs after logging in
Route::middleware(['auth:sanctum', 'active'])->prefix('vendor')->group(function () {
    // List vendor details of logged in user
    Route::get('/details', [VendorRegistrationController::class, 'showVendorDetails']);
    Route::post('/{vendor}/edit-details', [VendorRegistrationController::class, 'editVendorDetails']);
    Route::get('/{vendor}/view-manager', [VendorRegistrationController::class, 'showManagerDetails']);
    Route::patch('/managers-deatils/{vendor}',[VendorRegistrationController::class, 'updateManagerDetails']);
    Route::get('/{vendor}/services', [SelectServicesController::class, 'showServices']);
    Route::get('/{vendor}/show-documents', [DocumentsUploadController::class, 'showDocuments']);

    Route::post('/{vendor}/escalation-matrix', [EscalationMatrixController::class, 'store']);
    Route::patch('/escalation-matrix/{escalationmatrix}', [EscalationMatrixController::class, 'edit']);
    Route::get('/{vendor}/escalation-matrix', [EscalationMatrixController::class, 'show']);
    Route::post('/escalation-matrix/{escalationmatrix}/delete', [EscalationMatrixController::class, 'delete']);
    Route::get('/{vendor}/tickets', [VendorComplaintController::class, 'listComplaints']);
    Route::post('/vendor-comment/{complaint}', [VendorComplaintController::class, 'addComment']);
    Route::get('/list-buildings/{vendor}', [VendorBuildingController::class, 'listBuildings']);
    Route::get('/{vendor}/contracts', [ContractController::class, 'index']);
    Route::get('/{vendor}/list-contracts', [ContractController::class, 'listContracts']);

    //Dashboard Snags
    Route::get('/dashboard-snag-stats/{vendor}', [SnagDashboardController::class, 'tasks']);

    // Invoice create API
    Route::post('/{vendor}/create-invoice', [InvoiceController::class, 'store']);

    // WDA create API
    Route::post('/{vendor}/create-wda', [WDAController::class, 'store']);

    // List all WDAs
    Route::get('/{vendor}/wda', [WDAController::class, 'index']);

    // List invoices status
    Route::get('/{vendor}/invocies', [InvoiceController::class, 'index']);

    //List invoices
    Route::get('/invoices/{vendor}', [InvoiceController::class, 'listInvoice']);

    //Show Invoice
    Route::get('/invoice/{invoice}', [InvoiceController::class, 'show']);

    // Edit Invoice
    Route::post('/invoice/{invoice}', [InvoiceController::class, 'edit']);

    // Invoice dashboard
    Route::get('/dashboard-invoice-stats/{vendor}', [InvoiceController::class, 'stats']);

    // Show WDA
    Route::get('/wda/{wda}', [WDAController::class, 'show']);

    //Edit WDA
    Route::post('/wda/{wda}', [WDAController::class, 'edit']);

    //Escalation Matrix exists
    Route::get('/{vendor}/check-escalation-matrix', [EscalationMatrixController::class, 'exists']);

    // List all tenders
    Route::get('/tenders', [TenderController::class, 'index']);
    Route::post('/tenders/{tender}', [TenderController::class, 'store']);

    // TL number
    Route::get('/{vendor}/trade-licenses',[TLController::class,'show']);
    Route::post('/{vendor}/trade-licenses/update',[TLController::class,'update']);

    Route::get('/{vendor}/risk-policy',[DocumentsUploadController::class,'showRiskPolicy']);
    Route::post('/{vendor}/risk-policy/update',[DocumentsUploadController::class,'updateRiskPolicy']);

    //proposals
    Route::get('/{vendor}/proposals', [ProposalController::class, 'index']);

    //Items APIs
    Route::get('/{vendor}/items', [ItemsController::class, 'index']);
    Route::post('/{item}/item_management', [ItemsController::class,'updateItems']);
    Route::get('/{item}/view-item', [ItemsController::class,'viewItem']);

    //Sub Contractor APIs
    Route::get('/{vendor}/sub-contractors',[SubContractorsController::class,'index']);
    Route::post('/{vendor}/sub-contractor',[SubContractorsController::class,'store']);
    Route::post('/{vendor}/sub-contractor/{subContract}',[SubContractorsController::class,'edit']);
    Route::patch('/{vendor}/sub-contractor/{subContract}',[SubContractorsController::class,'update']);


    //Complaint create
    Route::post('/{vendor}/complaint',[VendorComplaintController::class,'create']);

    //Form Requests
    Route::get('/{vendor}/guest-registration',[GuestController::class,'fmlist']);
    Route::get('/{vendor}/move-in-out',[MoveInOutController::class,'fmlist']);
    Route::get('/{vendor}/fit-out',[FitOutFormsController::class,'fmlist']);
    Route::get('/{vendor}/residential-form',[ResidentialFormController::class,'fmlist']);
    Route::get('/{vendor}/accesscard-form',[AccessCardController::class,'fmlist']);
    Route::get('/{vendor}/salenoc-form',[SaleNocController::class,'fmlist']);
    Route::get('/{vendor}/flatvisitor-form',[FlatVisitorController::class,'index']);
});

// Technician Related APIs
Route::middleware(['auth:sanctum', 'active'])->prefix('technician')->group(function () {
    // Registration
    Route::post('/registration', [TechnicianController::class, 'registration']);
    //edit technician details
    Route::patch('/edit-details/{technician}', [TechnicianController::class, 'edit']);
    // List all buildings for logged in technician
    Route::get('/buildings', [TechnicianBuildingController::class, 'index']);
    Route::get('/tasks', [TasksController::class, 'index']);
    //List all technicians for a service
    Route::get('/{service}/technicians/{vendor}', [TechnicianController::class, 'index']);
    Route::get('/{vendor}/technicians', [TechnicianController::class, 'listTechnicians']);
    Route::get('/{vendor}/all-technicians', [TechnicianController::class, 'allTechnician']);
    Route::patch('/active-deactive/{technician}', [TechnicianController::class, 'activeDeactive']);
    Route::post('/attach-technician/{technician}', [TechnicianController::class, 'attachTechnician']);
    Route::post('/detach-technician/{technician}', [TechnicianController::class, 'detachTechnician']);
    Route::post('/assign-technician/{complaint}', [TechnicianController::class, 'assignTechnician']);
    Route::get('/{service}/list-technicians/{vendor}', [TechnicianController::class, 'technicianList']);
});

// Assets related APIs
Route::middleware(['auth:sanctum', 'active'])->prefix('assets')->group(function () {
    // List all assets for the technicians
    Route::get('/technican/assets', [AssetController::class, 'index']);
    // Service history for an asset
    Route::get('/maintenance-list/{technicianasset}', [AssetController::class, 'fetchAssetMaintenances']);

    Route::post('/maintenance', [AssetController::class, 'store']);
    Route::post('/maintenance/{assetMaintenance}/update-before', [AssetController::class, 'updateBefore']);
    Route::post('/maintenance/{assetMaintenance}/update-after', [AssetController::class, 'updateAfter']);

    // API to list asset details for the technician when he scans the QR code
    Route::get('/{asset}', [TechnicianController::class, 'fetchTechnicianAssetDetails']);

    //Vendor assets
    Route::get('/vendor/{vendor}', [AssetController::class, 'listAssets']);
    Route::post('/attach-asset/{asset}', [AssetController::class, 'attachAsset']);
    Route::get('/{asset}/technicians', [AssetController::class, 'listTechnicians']);
    Route::post('/vendor/{vendor}/create',[AssetController::class, 'create']);
    Route::get('/vendor/{vendor}/asset/{asset}',[AssetController::class, 'showAsset']);
    Route::post('/vendor/{vendor}/asset/{asset}',[AssetController::class, 'updateAsset']);

    //PPM APIs
    Route::post('/create/ppm', [PPMController::class, 'store']);
    Route::get('/{vendor}/ppm/', [PPMController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'active', 'active.gatekeeper'])->prefix('gatekeeper')->group(function () {
    Route::get('snags', [GatekeeperComplaintController::class, 'index']);

    Route::get('floors', [PatrollingController::class, 'featchAllFloors']);
    Route::post('store-patrolling/{building}', [PatrollingController::class, 'store']);

    // List all residents for an
    // Save visitor for a floor
    Route::post('visitor', [PatrollingController::class, 'storeVisitor']);

    // List all tenants for a building with flat name and searchable flat number option
    Route::get('{building}/tenants/{unit?}', [TenantsController::class, 'fetchAllTenants']);

    // List all visitors for a building
    Route::get('/building/{building}/visits', [GuestController::class, 'futureVisits']);

    // Notify tenants on visitor's entry
    Route::post('/notify-resident', [GuestController::class, 'notifyTenant']);

    // MoveIn MoveOut
    Route::get('/move-in-out',[MoveInOutController::class, 'list']);

    Route::get('/visitor-request',[GuestController::class, 'visitorRequest']);
    Route::post('/visitor-approval/{visitor}', [GuestController::class, 'visitorApproval']);
    Route::post('/verify-visitor/{visitor}', [MollakController::class, 'verifyVisitor']);
});
// Approve visitor request
Route::post('/gatekeeper/visitor-entry', [GuestController::class, 'visitorEntry'])->middleware(['auth:sanctum']);

// API to import services
Route::post('/import-services', [ServiceController::class, 'import']);

// about Community
Route::get('/building/{building}', [CommunityController::class, 'about']);

// rules and regulations
Route::get('/rules-regulations/{building}',[CommunityController::class, 'rules']);

// Emergency hotline Numbers
Route::get('/emergency-hotline/numbers/{building}',[CommunityController::class, 'emergencyHotline']);

// offer and Promotions
Route::get('/offer-promotions/{building}',[CommunityController::class, 'offerPromotions']);

// Visitor form
Route::post('/store-visitor', [GuestController::class, 'saveFlatVisitors']);

// Test Send SMS
Route::post('/send-sms', [MollakController::class, 'sendSMS']);
Route::post('/verify-sms-otp', [MollakController::class, 'verifyOTP']);

Route::get('/verify-contractor-request/{fitout}',[FitOutFormsController::class, 'verifyContractorRequest']);

//Webhooks
Route::post('/budget-budget_items',[MollakController::class, 'fetchbudget']);

Route::get('/testing',[MollakController::class, 'test']);

Route::get('/service-charge-period/{propertyId}',[MollakController::class,'ServicePeriods']);

Route::get('/test',[MollakController::class, 'testing']);

//App Versions
Route::get('/app-version',[AppController::class, 'version']);

//web enquiries
Route::post('/web-enquiry',[EnquiryController::class,'store']);

//webhook
// Route::post('/webhook',[MollakController::class,'webhook'])->middleware('check.MollakToken');
// Route::get('/webhook',[MollakController::class,'webhook'])->middleware('check.MollakToken');
Route::match(['get', 'post'], '/webhook', [MollakController::class, 'webhook'])
     ->middleware('check.MollakToken');

Route::post('/webhook/sync-invoice',[MollakController::class, 'invoiceWebhook'])->middleware('check.MollakToken');
Route::post('/webhook/sync-receipt',[MollakController::class, 'receiptWebhook'])->middleware('check.MollakToken');

//mollak
Route::post('/upload',[TestController::class, 'uploadAll'])->name('uploadAll');


Route::middleware(['authenticate.tally'])->group(function () {
    Route::get('/V1/getVouchers',[TallyIntigrationController::class,'getVouchers']);
});
Route::post('/mollak/wrapper', [TestController::class, 'forwardRequest']);

Route::post('/email-testing', [TestController::class, 'emailTriggering']);
