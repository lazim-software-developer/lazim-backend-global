<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFlatForResidentsRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RegisterWithEmiratesOrPassportRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\RegisterOwnersList;
use App\Jobs\Auth\ResendOtpEmail;
use App\Jobs\Building\AssignFlatsToTenant;
use App\Jobs\SendVerificationOtp;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\UserApproval;
use App\Models\UserApprovalAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function registerWithEmailPhone(RegisterRequest $request)
    {

        $userData = User::where(['email' => $request->get('email'), 'phone' => $request->get('mobile')]);
        if ($request->type == 'Owner') {
            $userData->where('owner_id', $request->get('owner_id'));
        }

        if ($userData->exists() && ($userData->first()->email_verified == 0)) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected to account verification page",
                'code' => 403,
                'type' => 'email'
            ]))->response()->setStatusCode(403);
        }

        // If email is verified,
        if ($userData->exists() && ($userData->first()->phone_verified == 0)) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected to account verification page",
                'code' => 403,
                'type' => 'phone'
            ]))->response()->setStatusCode(403);
        }

        // Check if user exists in our DB
        if (User::where(['email' => $request->email, 'phone' => $request->mobile, 'email_verified' => 1, 'phone_verified' => 1, 'owner_id' => $request->owner_id])->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'code' => 409,
            ]))->response()->setStatusCode(409);
        }

        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);

        // Check if flat exists
        if (!$flat) {
            return (new CustomResponseResource([
                'title' => 'flat_error',
                'message' => 'Flat selected by you doesnot exists',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        // Determine the type (tenant or owner)
        $type = $request->input('type', 'Owner');

        // Check if the given flat_id is already allotted to someone with active true
        $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1, 'role' => 'Tenant']);


        if ($type === 'Tenant' && $flatOwner->exists()) {
            return (new CustomResponseResource([
                'title' => 'flat_error',
                'message' => 'Looks like this flat is already allocated to someone!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        if ($type === 'Owner') {
            $queryModel = $flat->owners()->where('apartment_owners.id', $request->owner_id);
        } else {
            $queryModel = MollakTenant::where(['email' => $request->email, 'mobile' => $request->mobile, 'building_id' => $request->building_id, 'flat_id' => $request->flat_id]);
        }

        if (!$queryModel->exists()) {
            if ($type === 'Owner') {
                return (new CustomResponseResource([
                    'title' => 'mollak_error',
                    'message' => "Your details are not matching with Mollak data. Please use your Title Deed instead",
                    'code' => 400,
                ]))->response()->setStatusCode(400);
            } else {
                return (new CustomResponseResource([
                    'title' => 'mollak_error',
                    'message' => "Your details are not matching with Mollak data. Please use your Ejari document instead",
                    'status' => 'detailsNotMatching',
                    'code' => 400,
                ]))->response()->setStatusCode(400);
            }
        }

        // Fetch first name
        $firstName = $queryModel->value('name');

        // Identify role based on the type
        $role = Role::where('name', $type)->value('id');

        // If the check passes, store the user details in the users table
        // Fetch building
        $owner_association_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()?->owner_association_id;
        $building = Building::where('id', $request->building_id)->first();
        $user = User::create([
            'email' => $request->email,
            'first_name' => $firstName,
            'phone' => $request->mobile,
            'role_id' => $role,
            'active' => 1,
            'owner_association_id' => $owner_association_id,
            'owner_id' => $request->owner_id ?: null,
        ]);
        $connection = DB::connection('lazim_accounts');
        $created_by = $connection->table('users')->where(['type' => 'building', 'building_id' => $request->building_id])->first()?->id;
        if($created_by){
            $customerId = $connection->table('customers')->where('created_by', $created_by)->orderByDesc('customer_id')->first()?->customer_id + 1;
            $connection->table('customers')->insert([
                'customer_id' => $customerId,
                'name' => $firstName,
                'email'                => $request->email,
                'contact' => $request->mobile,
                'type' => $type,
                'lang' => 'en',
                'created_by' => $created_by,
                'is_enable_login' => 0,
            ]);
        }

        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $user->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' =>  null,
            'end_date' => $type === 'Tenant' ? $queryModel->value('end_date') : null,
            'active' => 1,
            'role' => $type,
            'owner_association_id' => $building->owner_association_id,
        ]);

        // $customer = $connection->table('customers')->where(['email'=> $request->email,
        //     'contact' => $request->mobile])->first();
        // $property = Flat::find($request->flat_id)?->property_number;
        // $connection->table('customer_flat')->insert([
        //     'customer_id' => $customer?->id,
        //     'flat_id' => $request->flat_id,
        //     'building_id' => $request->building_id,
        //     'property_number' => $property
        // ]);

        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));

        // Find all the flats that this user is owner of and attach them to flat_tenant table using the job
        if($customerId){
            AssignFlatsToTenant::dispatch($request->email, $request->mobile, $request->owner_id, $customerId, $type)->delay(now()->addSeconds(5));
        }

        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'code' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }

    public function registerWithDocument(RegisterWithEmiratesOrPassportRequest $request)
    {
        $userData = User::where(['email' => $request->get('email')]);
        if ($request->type == 'Owner') {
            $userData->where('owner_id', $request->get('owner_id'));
        }


        if ($userData->exists() && ($userData->first()->email_verified == 0)) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected to account verification page",
                'code' => 403,
                'type' => 'email'
            ]))->response()->setStatusCode(403);
        }

        // If email is verified,
        if ($userData->exists() && ($userData->first()->phone_verified == 0)) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected to account verification page",
                'code' => 403,
                'type' => 'phone'
            ]))->response()->setStatusCode(403);
        }

        // Check if user exists in our DB
        if (User::where(['email' => $request->email, 'email_verified' => 1, 'phone_verified' => 1])->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'code' => 409,
            ]))->response()->setStatusCode(409);
        }

        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);

        // Check if flat exists
        if (!$flat) {
            return (new CustomResponseResource([
                'title' => 'flat_error',
                'message' => 'Flat selected by you doesnot exists',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        // Check if the given flat_id is already allotted to someone with active true
        $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1])->exists();

        if ($flatOwner) {
            return (new CustomResponseResource([
                'title' => 'flat_error',
                'message' => 'Looks like this flat is already allocated to someone!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        // Determine the type (tenant or owner)
        $type = $request->input('type', 'Owner');


        // Identify role based on the type
        $role = Role::where('name', $type)->value('id');

        // If the check passes, store the user details in the users table
        $building = Building::where('id', $request->building_id)->first();
        $owner_association_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()?->owner_association_id;

        $user = User::create([
            'email' => $request->email, // Assuming email is still provided for communication
            'first_name' => $request->name,
            'phone' => $request->mobile, // Assuming phone is still provided for communication
            'role_id' => $role,
            'active' => 0,
            'owner_association_id' => $owner_association_id,
        ]);

        $connection = DB::connection('lazim_accounts');
        $created_by = $connection->table('users')->where(['type' => 'building', 'building_id' => $request->building_id])->first()?->id;
        if($created_by){
            $customerId = $connection->table('customers')->where('created_by', $created_by)->orderByDesc('customer_id')->first()?->customer_id + 1;
            $connection->table('customers')->insert([
                'customer_id' => $customerId,
                'name' => $request->name,
                'email'  => $request->email,
                'contact' => $request->mobile,
                'type' => $type,
                'lang' => 'en',
                'created_by' => $created_by,
                'is_enable_login' => 0,
            ]);
        }

        $imagePath = optimizeDocumentAndUpload($request->document, 'dev');
        $emirates = optimizeDocumentAndUpload($request->emirates_document, 'dev');
        $passport = optimizeDocumentAndUpload($request->passport_document, 'dev');

        $oam_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first();
        $oam = OwnerAssociation::find($oam_id->owner_association_id ?: auth()->user()->ownerAssociation->id);

        $userApproval = UserApproval::create([
            'user_id' => $user->id,
            'document' => $imagePath,
            'document_type' => $request->type == 'Owner' ? 'Title Deed' : 'Ejari',
            'emirates_document' => $emirates,
            'emirates_document_expiry_date' => $request->has('emirates_document_expiry_date') ? $request->emirates_document_expiry_date : null,
            'passport' => $passport,
            'passport_expiry_date' => $request->has('passport_expiry_date') ? $request->passport_expiry_date : null,
            'flat_id' => $request->flat_id,
            'owner_association_id' => $oam?->id,
        ]);

        // Store details to UserApprovalAudit table for status history
        UserApprovalAudit::create([
            'user_approval_id' => $userApproval->id,
            'document' => $imagePath,
            'document_type' => $request->type == 'Owner' ? 'Title Deed' : 'Ejari',
            'emirates_document' => $emirates,
            'passport' => $passport,
            'owner_association_id' => $oam?->id,
        ]);

        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $user->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' =>  $request->has('start_date') ? $request->start_date : now(),
            'end_date' => $request->has('end_date') ? $request->end_date : null,
            'active' => 1,
            'role' => $type,
            'owner_association_id' => $owner_association_id,
            'residing_in_same_flat' => $request->has('residing') ? $request->residing : 0,
        ]);

        $customer = $connection->table('customers')->where(['email'=> $request->email,
            'contact' => $request->mobile])->first();
        $property = Flat::find($request->flat_id)?->property_number;
        if($customer && $property){
            $connection->table('customer_flat')->insert([
                'customer_id' => $customer?->id,
                'flat_id' => $request->flat_id,
                'building_id' => $request->building_id,
                'property_number' => $property
            ]);
        }

        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));

        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'code' => 201,
            'status' => 'verificationPending'
        ]))->response()->setStatusCode(201);
    }

    public function reuploadDocument(Request $request,UserApproval $resident){
        if($resident->status == null){
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Your last changes is not yet approved!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }
        if($resident->status == 'approved'){
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Your account is already approved!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }
        $request->validate([
            'document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
            'emirates_document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
            'passport_document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $imagePath = optimizeDocumentAndUpload($request->document, 'dev');
        $emirates = optimizeDocumentAndUpload($request->emirates_document, 'dev');
        $passport = optimizeDocumentAndUpload($request->passport_document, 'dev');

        $userApproval = $resident->update([
            'document' => $imagePath,
            'emirates_document' => $emirates,
            'passport_document' => $passport,
            'status' => null,
            'remarks' => null,
        ]);

        UserApprovalAudit::create([
            'user_approval_id' => $resident->id,
            'document' => $imagePath,
            'document_type' => $resident->document_type,
            'emirates_document' => $emirates,
            'passport' => $passport,
            'owner_association_id' => $resident->owner_association_id,
        ]);

        return (new CustomResponseResource([
            'title' => 'Document submitted!',
            'message' => "Document submitted succesfully!",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function documentStatus(UserApproval $resident)
    {
        if($resident->status == null){
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have already uploaded documents, approve pending!',
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }
        if($resident->status == 'approved'){
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Your account is already approved!',
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        return response()->noContent();
    }

    public function viewDocuments(UserApproval $resident)
    {
        return [
            'document_type' => strtolower($resident->document_type),
            'document' => env('AWS_URL').'/'.$resident->document,
            'emirates_document' => env('AWS_URL').'/'.$resident->emirates_document,
            'passport' => env('AWS_URL').'/'.$resident->passport
        ];
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        // Validate the type and contact_value
        $type = $request->type;
        $contactValue = $request->contact_value;

        // Generate OTP
        $otp = rand(1000, 9999);

        if ($type == 'email') {
            $user = user::where('email', $contactValue)->when($request->has('owner_id'), function ($query) use ($request) {
                return $query->where('owner_id', $request->owner_id);
            })->first();
        } else {
            $user = user::where('phone', $contactValue)->when($request->has('owner_id'), function ($query) use ($request) {
                return $query->where('owner_id', $request->owner_id);
            })->first();
        }

        if ($user) {

            // Check if email or phone is already verified. If yes, don't need to verify again

            if (($type == 'email' && $user->email_verified) || ($type == 'phone' && $user->phone_verified)) {
                return (new CustomResponseResource([
                    'title' => 'Error',
                    'message' => 'The provided ' . $type . ' is already verified.',
                    'code' => 404,
                ]))->response()->setStatusCode(404);
            }

            // Store OTP in the database
            DB::table('otp_verifications')->updateOrInsert(
                ['type' => $type, 'contact_value' => $contactValue],
                ['otp' => $otp]
            );

            // If type is email, send the OTP to the email
            ResendOtpEmail::dispatch($user, $otp, $type)->delay(now()->addSeconds(5));

            //TODO: If type is phone, you can integrate with an SMS service to send the OTP
            // (This part is left out for now as it depends on the SMS service)

            return (new CustomResponseResource([
                'title' => 'Success',
                'message' => 'OTP sent successfully!',
                'code' => 200,
            ]))->response()->setStatusCode(200);
        }

        return (new CustomResponseResource([
            'title' => 'Error',
            'message' => 'The provided ' . $type . ' is not registered in our system.',
            'code' => 404,
        ]))->response()->setStatusCode(404);
    }

    public function ownerList(Flat $flat)
    {

        $owners = $flat->owners()->get();

        $owners = $owners->filter(function ($owner) {
            if (!$owner->users()
                ->where('email_verified', 1)
                ->where('phone_verified', 1)
                ->exists()) {
                return $owner;
            };
        });

        return RegisterOwnersList::collection($owners);
    }

    public function ownerDetails(ApartmentOwner $owner)
    {
        return ['data' => [
            'email' => $owner->email,
            'phone' => $owner->mobile
        ]];
    }

    public function allOwners(Request $request)
    {

        $users = User::where('email', $request->email)->pluck('owner_id');
        $owners = ApartmentOwner::whereIn('id', $users)->get();

        return RegisterOwnersList::collection($owners);
    }
     public function addFlat(AddFlatForResidentsRequest $request)
    {
        $userData = User::find(auth()->id());

        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);

        // check if the flat is already registered to the same user
        $flatTenant = FlatTenant::where('flat_id', $request->flat_id)->where('tenant_id', $userData->id)->first();
        if ($flatTenant) {
            return (new CustomResponseResource([
                'title' => 'Registration error',
                'message' => 'Looks like you have already registered for this flat!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        if($request->type == 'Tenant'){
            // Check if the given flat_id is already allotted to someone with active true
            $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1, 'role'=> 'Tenant'])->exists();
            if ($flatOwner) {
                return (new CustomResponseResource([
                    'title' => 'Registration error',
                    'message' => 'Looks like this flat is already allocated to someone!',
                    'code' => 400,
                ]))->response()->setStatusCode(400);
            }
        }

        // Determine the type (tenant or owner)
        $type = $request->input('type', 'Owner');

        $owner_association_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()?->owner_association_id;

        $connection = DB::connection('lazim_accounts');
        $created_by = $connection->table('users')->where(['type' => 'building', 'building_id' => $request->building_id])->first()?->id;
        if($created_by){
            $customerId = $connection->table('customers')->where('created_by', $created_by)->orderByDesc('customer_id')->first()?->customer_id + 1;
            $connection->table('customers')->insert([
                'customer_id' => $customerId,
                'name' => $userData->name,
                'email'  => $userData->email,
                'contact' => $userData->mobile,
                'type' => $type,
                'lang' => 'en',
                'created_by' => $created_by,
                'is_enable_login' => 0,
            ]);
        }

        $userApproval = UserApproval::where('user_id', $userData->id)->first();
        $imagePath = optimizeDocumentAndUpload($request->document, 'dev');

        $userApproval = UserApproval::create([
            'user_id' => $userData->id,
            'document' => $imagePath,
            'document_type' => $request->type == 'Owner' ? 'Title Deed' : 'Ejari',
            'emirates_document' => $userApproval->emirates_document,
            'emirates_document_expiry_date' => $userApproval->emirates_document_expiry_date,
            'passport' => $userApproval->passport,
            'passport_expiry_date' => $userApproval->passport_expiry_date,
            'flat_id' => $request->flat_id,
            'owner_association_id' => $owner_association_id,
        ]);

        // Store details to UserApprovalAudit table for status history
        UserApprovalAudit::create([
            'user_approval_id' => $userApproval->id,
            'document' => $imagePath,
            'document_type' => $request->type == 'Owner' ? 'Title Deed' : 'Ejari',
            'emirates_document' => $userApproval->emirates_document,
            'passport' => $userApproval->passport,
            'owner_association_id' => $owner_association_id,
        ]);

        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $userData->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' =>  $request->has('start_date') ? $request->start_date : now(),
            'end_date' => $request->has('end_date') ? $request->end_date : null,
            'active' => 1,
            'role' => $type,
            'owner_association_id' => $owner_association_id,
            'residing_in_same_flat' => $request->has('residing') ? $request->residing : 0,
        ]);

        $customer = $connection->table('customers')->where(['email'=> $userData->email,
            'contact' => $userData->mobile])->first();
        $property = Flat::find($request->flat_id)?->property_number;
        if($customer && $property){
            $connection->table('customer_flat')->insert([
                'customer_id' => $customer?->id,
                'flat_id' => $request->flat_id,
                'building_id' => $request->building_id,
                'property_number' => $property
            ]);
        }

        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We have sent request to admin once approved you will be able to see that flat in your profile",
            'code' => 201,
            'status' => 'verificationPending'
        ]))->response()->setStatusCode(201);
    }
}
