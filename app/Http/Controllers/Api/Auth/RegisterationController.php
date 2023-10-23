<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\CustomResponseResource;
use App\Jobs\Auth\ResendOtpEmail;
use App\Jobs\Building\AssignFlatsToTenant;
use App\Jobs\SendVerificationOtp;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class RegisterationController extends Controller
{
    public function register(RegisterRequest $request) {
        // Fetch the flat using the provided flat_id
        $flat = Flat::find($request->flat_id);
    
        // Check if flat exists
        if (!$flat) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Please select a flat',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Check if the given flat_id is already alloted to someone with active true
        $flatOwner = DB::table('flat_tenants')->where(['flat_id' => $flat->id, 'active' => 1])->exists();
    
        if ($flatOwner) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Looks like this flat is already allocated to someone!',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }
    
        // Check the owner details based on the provided information
        $ownerQuery = $flat->owners();
    
        if ($request->email && $request->mobile) {
            $ownerQuery->where('email', $request->email)
                       ->where('mobile', $request->mobile);
        } elseif ($request->passport) {
            $ownerQuery->where('passport', $request->passport);
        } elseif ($request->emirates_id) {
            $ownerQuery->where('emirates_id', $request->emirates_id);
        }
    
        if
        (!$ownerQuery->exists()) {
            $errorMessage = 'Your details are not matching with Mollak data. Please enter valid details.';

            if ($request->email && $request->mobile) {
                $errorMessage = 'Your details are not matching with Mollak data. Try registering using Passport or Emirates ID.';
            } elseif ($request->passport || $request->emirates_id) {
                $errorMessage = 'Your details are not matching with Mollak data. Try registering with Email and Phone.';
            }

            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => $errorMessage,
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }
        
        // If the check passes, store the user details in the users table
        $user = User::create([
            'email' => $request->email,
            'first_name' => $ownerQuery->first()->name,
            'phone' => $request->mobile,
            'role_id' => 1,
            'active' => 1
        ]);

        // Store details to Flat tenants table
        FlatTenant::create([
            'flat_id' => $request->flat_id,
            'tenant_id' => $user->id,
            'primary' => true,
            'building_id' => $request->building_id,
            'start_date' => now(), //This needs to change - Fetch from Mollak
            'active' => 1
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
