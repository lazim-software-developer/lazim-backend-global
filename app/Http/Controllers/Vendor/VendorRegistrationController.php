<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CompanyDetailsRequest;
use App\Http\Requests\Vendor\ManagerDetailsRequest;
use App\Http\Requests\Vendor\VendorRegisterRequest;
use App\Http\Resources\CustomResponseResource;
use App\Jobs\SendVerificationOtp;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorManager;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function registration(VendorRegisterRequest $request)
    {
        // Check if the user is already registered and verified
        if(User::where(['email' => $request->email, 'phone' => $request->mobile])
            ->where(function ($query) {
                $query->where('email_verified', 0);
                $query->orWhere('phone_verified', 0);
            })->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => "Your account is not verified. You'll be redirected account verification page",
                'code' => 403, 
            ]))->response()->setStatusCode(403);
        }

        if (User::where(['email' => $request->email, 'phone' => $request->mobile, 'email_verified' => 1, 'phone_verified' => 1])->exists()) {
            return (new CustomResponseResource([
                'title' => 'account_present',
                'message' => 'Your email is already registered in our application. Please try login instead!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }
        
        $role = Role::where('name', 'Vendor')->value('id');
        $request->merge(['first_name' => $request->name,'active' => 1,'role_id' => $role]);

        $user = User::create($request->all());

        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));
        
        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'code' => 201,
            'status' => 'success',
            'data' => $user
        ]))->response()->setStatusCode(201);
    }

    public function companyDetails(CompanyDetailsRequest $request)
    {
        $request->merge(['status' => 'pending']);

        $vendor = Vendor::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Company Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $vendor
        ]))->response()->setStatusCode(201);
    }

    public function managerDetails(ManagerDetailsRequest $request)
    {
        $manager = VendorManager::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Vendor Manager Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $manager
        ]))->response()->setStatusCode(201);
    }

    public function showManagerDetails()
    {
        $vendor_id =Vendor::where('owner_id', auth()->user()->id)->first()->id;
        $manager = VendorManager::where('vendor_id',$vendor_id)->first();
        return (new CustomResponseResource([
            'title' => 'Vendor Manager Details fetched',
            'message' => "",
            'code' => 200,
            'status' => 'success',
            'data' => $manager
        ]))->response()->setStatusCode(200);
    }
}
