<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RegisterWithEmiratesOrPassportRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Resources\CustomResponseResource;
use App\Jobs\Auth\ResendOtpEmail;
use App\Jobs\Building\AssignFlatsToTenant;
use App\Jobs\SendVerificationOtp;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class RegisterationController extends Controller
{
    public function registerWithEmailPhone(RegisterRequest $request) {
        if(User::where(['email' => $request->email, 'phone' => $request->mobile])
            ->where('email_verified', 0)->orWhere('phone_verified', 0)->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected account verification page",
                'errorCode' => 403, 
            ]))->response()->setStatusCode(400);
        }

        // Check if user exists in our DB
        if (User::where(['email' => $request->email, 'phone' => $request->mobile, 'email_verified' => 1, 'phone_verified' => 1])->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }

        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);
    
        // Check if flat exists
        if (!$flat) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Flat selected by you doesnot exists',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Check if the given flat_id is already allotted to someone with active true
        $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1])->exists();
    
        if ($flatOwner) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Looks like this flat is already allocated to someone!',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Determine the type (tenant or owner)
        $type = $request->input('type', 'Owner');
    
        if ($type === 'Owner') {
            $queryModel = $flat->owners()->where('email', $request->email)->where('mobile', $request->mobile);
        } else {
            $queryModel = MollakTenant::where(['email' => $request->email, 'mobile' => $request->mobile, 'building_id'=> $request->building_id, 'flat_id' => $request->flat_id]);
        }
    
        if (!$queryModel->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "Your details are not matching with Mollak data. Please enter valid details.",
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }
    
        // Fetch first name
        $firstName = $queryModel->value('name');
    
        // Identify role based on the type
        $role = Role::where('name', $type)->value('id');
    
        // If the check passes, store the user details in the users table
        $user = User::create([
            'email' => $request->email,
            'first_name' => $firstName,
            'phone' => $request->mobile,
            'role_id' => $role,
            'active' => 1,
            'building_id' => $request->building_id
        ]);
    
        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $user->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' => $type === 'Tenant' ? $queryModel->value('start_date') : null,
            'end_date' => $type === 'Tenant' ? $queryModel->value('end_date') : null,
            'active' => 1,
            'role' => $type
        ]);
    
        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));
    
        // Find all the flats that this user is owner of and attach them to flat_tenant table using the job
        AssignFlatsToTenant::dispatch($request->email)->delay(now()->addSeconds(5));
    
        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'errorCode' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }
    
    public function registerWithEmiratesOrPassport(RegisterWithEmiratesOrPassportRequest $request) {
        // Check if user with same email already present
        if(User::where(['email' => $request->email, 'phone' => $request->mobile])->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }

        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);
    
        // Check if flat exists
        if (!$flat) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Flat selected by you doesnot exists',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Check if the given flat_id is already allotted to someone with active true
        $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1])->exists();
    
        if ($flatOwner) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Looks like this flat is already allocated to someone!',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Determine the type (tenant or owner)
        $type = $request->input('type', 'Owner');
        $queryModel = null;

        $key = $request->has('passport') ? 'passport' : 'emirates_id';
        $value = $request->input($key);

        if ($type === 'Owner') {
            $queryModel = $flat->owners()->where($key, $value);
        } else {
            $queryModel = MollakTenant::where([$key => $value, 'building_id' => $request->building_id, 'flat_id' => $request->flat_id]);
        }
    
        if (!$queryModel || !$queryModel->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "Your details are not matching with Mollak data. Please enter valid details.",
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }
    
        // Fetch first name
        $firstName = $queryModel->value('name');
    
        // Identify role based on the type
        $role = Role::where('name', $type)->value('id');
    
        // If the check passes, store the user details in the users table
        $user = User::create([
            'email' => $request->email, // Assuming email is still provided for communication
            'first_name' => $firstName,
            'phone' => $request->mobile, // Assuming phone is still provided for communication
            'role_id' => $role,
            'active' => 1
        ]);

        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $user->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' => $type === 'Tenant' ? $queryModel->value('start_date') : null,
            'end_date' => $type === 'Tenant' ? $queryModel->value('end_date') : null,
            'active' => 1,
            'role' => $type
        ]);
    
        // Send email after 5 seconds (if email is provided)
        if ($request->email) {
            SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));
        }
    
        // Find all the flats that this user is owner of and attach them to flat_tenant table using the job
        if ($request->email) {
            AssignFlatsToTenant::dispatch($request->email)->delay(now()->addSeconds(5));
        }

        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "Registration was successful. We've sent a verification code to it your email. Please verify to continue using the application.",
            'errorCode' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }    

    public function resendOtp(ResendOtpRequest $request)
    {
        // Validate the type and contact_value
        $type = $request->type;
        $contactValue = $request->contact_value;

        // Generate OTP
        $otp = rand(1000, 9999);

        if($type == 'email') {
            $user = user::where('email', $contactValue)->first();
        } else {
            $user = user::where('phone', $contactValue)->first();
        }

        if($user) {

            // Check if email or phone is already verified. If yes, don't need to verify again

            if(($type == 'email' && $user->email_verified) || ($type == 'phone' && $user->phone_verified)) {
                return (new CustomResponseResource([
                    'title' => 'Error',
                    'message' => 'The provided '.$type.' is already verified.',
                    'errorCode' => 404,
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
                'errorCode' => 200,
            ]))->response()->setStatusCode(200);
        }
        
        return (new CustomResponseResource([
            'title' => 'Error',
            'message' => 'The provided '.$type.' is not registered in our system.',
            'errorCode' => 404,
        ]))->response()->setStatusCode(404);
    }

}
