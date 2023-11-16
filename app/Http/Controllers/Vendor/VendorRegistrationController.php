<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CompanyDetailsRequest;
use App\Http\Requests\Vendor\ManagerDetailsRequest;
use App\Http\Requests\Vendor\VendorRegisterRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorManagerResource;
use App\Http\Resources\Vendor\VendorResource;
use App\Jobs\SendVerificationOtp;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorManager;

class VendorRegistrationController extends Controller
{
    public function registration(VendorRegisterRequest $request)
    {
        // Check if the user is already registered and verified
        $userData = User::where(['email' => $request->get('email'), 'phone' => $request->get('phone')]);

        // if user exists
        if($userData->exists()){
            // If not verified, redirect to verification page
            if ($userData->exists() && ($userData->first()->email_verified == 0 || $userData->first()->phone_verified == 0)) {
                return (new CustomResponseResource([
                    'title' => 'redirect_verification',
                    'message' => "Your account is not verified. You'll be redirected account verification page",
                    'code' => 403,
                    'data' => $userData->first(),
                ]))->response()->setStatusCode(403);
            }

            // Check if company details are already added. If not redirect to company details page
            if (!$userData->first()->vendors()->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_company_details',
                    'message' => "You have not updated company details. You'll be redirected company details page",
                    'code' => 403,
                    'data' => $userData->first(),
                ]))->response()->setStatusCode(403);
            }

            // Check if manager details are added, if not redirect to manager details page
            $vendor = $userData->first()->vendors()->first();

            if (!$vendor->managers()->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_managers',
                    'message' => "You have not updated manager details. You'll be redirected manger details page",
                    'code' => 403,
                    'data' => $vendor,
                ]))->response()->setStatusCode(403);
            }

            // Check if user exists in our DB
            if (User::where(['email' => $request->email, 'phone' => $request->phone, 'email_verified' => 1, 'phone_verified' => 1])->exists()) {
                return (new CustomResponseResource([
                    'title' => 'account_present',
                    'message' => 'Your email is already registered in our application. Please try login instead!',
                    'code' => 400,
                ]))->response()->setStatusCode(400);
            }
        }

        $role = Role::where('name', 'Vendor')->value('id');
        $request->merge(['first_name' => $request->name, 'active' => 1, 'role_id' => $role]);

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
        $request->merge([
            'status' => 'pending',
            'name'   => User::find($request->owner_id)->first_name,
        ]);

        $vendor = Vendor::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Company Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $vendor
        ]))->response()->setStatusCode(201);
    }

    public function managerDetails(ManagerDetailsRequest $request, Vendor $vendor)
    {
        $request->merge(['vendor_id' => $vendor->id]);

        $manager = VendorManager::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Vendor Manager Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $manager
        ]))->response()->setStatusCode(201);
    }

    public function showManagerDetails(Vendor $vendor)
    {
        $manager = VendorManager::where('vendor_id', $vendor->id)->first();
       
        return new VendorManagerResource($manager);
    }

    // Show vendor details of logged in user 
    public function showVendorDetails() {
        return new VendorResource(auth()->user()->vendors()->first());
    }
}
