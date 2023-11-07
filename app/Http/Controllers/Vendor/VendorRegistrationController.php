<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorRegisterRequest;
use App\Jobs\SendVerificationOtp;
use App\Models\Master\Role;
use App\Models\User\User;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function registration(VendorRegisterRequest $request)
    {
        // Check if the user is already registered and verified
        if(User::where(['email' => $request->email, 'phone' => $request->mobile])
            ->where('email_verified', 0)->orWhere('phone_verified', 0)->exists()) {
            return response()->jason([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected account verification page",
                'errorCode' => 403, 
            ])->setStatusCode(403);
        }

        if (User::where(['email' => $request->email, 'phone' => $request->mobile, 'email_verified' => 1, 'phone_verified' => 1])->exists()) {
            return response()->jason([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'errorCode' => 400,
            ])->setStatusCode(400);
        }
        
        $role = Role::where('name', 'Vendor')->value('id');
        $request->merge(['first_name' => $request->name,'active' => 1,'role_id' => $role]);

        $user = User::create($request->all());

        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));
        return "success";
    }
}
