<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        $otpEntry = DB::table('otp_verifications')
                        ->where('type', $request->type)
                        ->where('contact_value', $request->contact_value)
                        ->first();

        if (!$otpEntry || $otpEntry->otp !== $request->otp) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Invalid OTP. Please try again.',
                'errorCode' => 400, 
            ]))->response()->setStatusCode(400);
        }

        // If OTP matches, you can set the user's email as verified in the users table or any other logic you want to implement
        
        if($request->type == 'email') {
            User::where('email', $request->contact_value)->update(['email_verified' => true]);
        } else {
            User::where('phone', $request->contact_value)->update(['phone_verified' => true]);
        }

        // Delete the OTP entry after successful verification
        DB::table('otp_verifications')->where('id', $otpEntry->id)->delete();

        return response()->json([
            'message' => 'Email successfully verified.',
            'status' => 'success'
        ], 200);
    }
    
}
