<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditTechnicianRequest;
use App\Http\Requests\EditVendorRequest;
use App\Http\Requests\Vendor\CompanyDetailsRequest;
use App\Http\Requests\Vendor\ManagerDetailsRequest;
use App\Http\Requests\Vendor\VendorRegisterRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\ListOAResource;
use App\Http\Resources\Vendor\VendorManagerResource;
use App\Http\Resources\Vendor\VendorResource;
use App\Jobs\SendVerificationOtp;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function registration(VendorRegisterRequest $request)
    {
        // Check if user exists in our DB
        $userData = User::where(['email' => $request->get('email'), 'phone' => $request->get('phone')]);

        // if user exists
        if ($userData->exists()) {

            // If not verified, redirect to verification page
            //|| $userData->first()->phone_verified == 0
            if ($userData->first()->email_verified == 0) {
                return (new CustomResponseResource([
                    'title' => 'redirect_verification',
                    'message' => "Your account is not verified. You'll be redirected to account verification page",
                    'code' => 403,
                    'data' => $userData->first(),
                ]))->response()->setStatusCode(403);
            }

            //&& $userData->first()->phone_verified
            // if ($userData->first()->email_verified) {
            //     return (new CustomResponseResource([
            //         'title' => 'account_present',
            //         'message' => 'Your details is already registered in our application. Please try login instead!',
            //         'code' => 400,
            //     ]))->response()->setStatusCode(400);
            // }

            // Check if company details are already added. If not redirect to company details page
            if (!$userData->first()->vendors()->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_company_details',
                    'message' => "You have not updated company details. You'll be redirected to company details page",
                    'code' => 403,
                    'data' => $userData->first(),
                ]))->response()->setStatusCode(403);
            }

            // Check if manager details are added, if not redirect to manager details page
            $vendor = $userData->first()->vendors()->first();

            if (!$vendor->managers()->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_managers',
                    'message' => "You have not updated manager details. You'll be redirected to manger details page",
                    'code' => 403,
                    'data' => $vendor,
                ]))->response()->setStatusCode(403);
            }

            // check if vendor has selected any service
            if (!$vendor->services()->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_services',
                    'message' => "You have not selected services. You'll be redirected to services page",
                    'code' => 403,
                    'data' => $vendor,
                ]))->response()->setStatusCode(403);
            }

            $documents = Document::where('documentable_id', $vendor->id);
            //check if vendor has uploaded documnets
            if (!$documents->exists()) {
                return (new CustomResponseResource([
                    'title' => 'redirect_documents',
                    'message' => "You have not uploaded all documents. You'll be redirected to documents page",
                    'code' => 403,
                    'data' => $vendor,
                ]))->response()->setStatusCode(403);
            }

            if($vendor){
                $isAttached = $vendor->ownerAssociation()->wherePivot('owner_association_id', $request->owner_association_id)->exists();

                if ($isAttached) {
                    return (new CustomResponseResource([
                        'title' => 'vendor_already_exists',
                        'message' => "This vendor is already registered with this Owner Association.",
                        'code' => 403, // Conflict status code
                        'data' => $vendor,
                    ]))->response()->setStatusCode(403);
                }
                $type = $userData->first()?->role->name;

                $vendor->ownerAssociation()->attach($request->owner_association_id, ['from' => now()->toDateString(),'active' =>false,'type' => $request->role]);
                return (new CustomResponseResource([
                    'title' => 'vendor_exists',
                    'message' => "You have successfully registered with the new Owner Association. They will get back to you soon!",
                    'code' => 200,
                    'status' => 'success',
                    'data' => $vendor,
                ]))->response()->setStatusCode(200);
            }

        } else {
            $existingEmail = User::where(['email' => $request->email])->first();
            $existingPhone = User::where(['phone' => $request->phone])->first();

            // Check if user exists in our DB
            if ($existingEmail) {
                if ($existingEmail->email_verified) {
                    return (new CustomResponseResource([
                        'title' => 'account_present',
                        'message' => 'Your email is already registered in our application.',
                        'code' => 400,
                    ]))->response()->setStatusCode(400);
                } else {
                    return (new CustomResponseResource([
                        'title' => 'redirect_verification',
                        'message' => "Your email is not verified. You'll be redirected to account verification page",
                        'code' => 403,
                        'data' => $existingEmail,
                    ]))->response()->setStatusCode(403);
                }
            }

            //->phone_verified
            if ($existingPhone) {
                if ($existingPhone) {
                    return (new CustomResponseResource([
                        'title' => 'account_present',
                        'message' => 'Your phone is already registered in our application.',
                        'code' => 400,
                    ]))->response()->setStatusCode(400);
                }
                // else {
                //     return (new CustomResponseResource([
                //         'title' => 'redirect_verification',
                //         'message' => "Your phone is not verified. You'll be redirected to account verification page",
                //         'code' => 403,
                //         'data' => $existingEmail,
                //     ]))->response()->setStatusCode(403);
                // }
            }
        }

        $role = Role::where('name', 'Vendor')->value('id');
        if($request->has('role') && isset($request->role) && $request->role === 'Property Manager'){
            $role = Role::where('name','Facility Manager')->value('id');
        }
        $request->merge(['first_name' => $request->name, 'active' => 1, 'role_id' => $role]);

        $user = User::create($request->all());

        // Send email after 5 seconds
        SendVerificationOtp::dispatch($user)->delay(now()->addSeconds(5));

        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'code' => 201,
            'status' => 'success',
            'data' => $user,
        ]))->response()->setStatusCode(201);
    }

    public function companyDetails(CompanyDetailsRequest $request)
    {
        $request->merge([
            'name' => User::find($request->owner_id)->first_name,
        ]);
        $user = User::find($request->owner_id);
        $vendor = Vendor::create($request->all());
        $type = OwnerAssociation::where('id',$request->owner_association_id)->first()?->role;

        $user->ownerAssociation()->attach($request->owner_association_id, ['from' => now()->toDateString()]);
        $vendor->ownerAssociation()->attach($request->owner_association_id, ['from' => now()->toDateString(),'type'=> $type]);

        $doc = Document::create([
            "name" => "risk_policy",
            "document_library_id" => DocumentLibrary::where('name', 'Risk policy')->first()->id,
            "owner_association_id" => $request->owner_association_id,
            "status" => 'pending',
            "documentable_id" => $vendor->id,
            "expiry_date" => $request->risk_policy_expiry,
            "documentable_type" => Vendor::class,
        ]);

        return (new CustomResponseResource([
            'title' => 'Company Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $vendor,
        ]))->response()->setStatusCode(201);
    }

    public function managerDetails(ManagerDetailsRequest $request, Vendor $vendor)
    {
        $request->merge(['vendor_id' => $vendor->id]);

        $existingVendorEmail = VendorManager::where(['email' => $request->email])->first();
        $existingVendorPhone = VendorManager::where(['phone' => $request->phone])->first();
        // Check if user exists in our DB
        if ($existingVendorEmail) {
            return (new CustomResponseResource([
                // 'title' => 'account_present',
                'message' => 'Your email is already registered in our application!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        if ($existingVendorPhone) {
            return (new CustomResponseResource([
                // 'title' => 'account_present',
                'message' => 'Your phone is already registered in our application!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        $manager = VendorManager::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Vendor Manager Details entered successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $manager,
        ]))->response()->setStatusCode(201);
    }

    public function updateManagerDetails(ManagerDetailsRequest $request, Vendor $vendor)
    {
        $managerId = VendorManager::where('vendor_id', $vendor->id)->first()?->id;
        $request->merge(['vendor_id' => $vendor->id]);

        $existingVendorEmail = VendorManager::where('email', $request->email)
            ->where('id', '!=', $managerId)
            ->first();

        $existingVendorPhone = VendorManager::where('phone', $request->phone)
            ->where('id', '!=', $managerId)
            ->first();

        if ($existingVendorEmail) {
            return (new CustomResponseResource([
                'message' => 'Your email is already registered in our application!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        if ($existingVendorPhone) {
            return (new CustomResponseResource([
                'message' => 'Your phone is already registered in our application!',
                'code' => 400
            ]))->response()->setStatusCode(400);
        }
        if($managerId){
            $manager = VendorManager::find($managerId)->update($request->all());
        }
        else{
            $manager = VendorManager::create($request->all());
        }

        return (new CustomResponseResource([
            'title' => 'Manager Details updated successfully!',
            'message' => "Manager Details updated successfully!",
            'code' => 200,
            'status' => 'success',
            'data' => $manager,
        ]))->response()->setStatusCode(201);
    }

    public function showManagerDetails(Vendor $vendor)
    {
        $manager = VendorManager::where('vendor_id', $vendor->id)->first();

        return new VendorManagerResource($manager);
    }

    // Show vendor details of logged in user
    public function showVendorDetails()
    {
        return new VendorResource(auth()->user()->vendors()->first());
    }

    public function editVendorDetails(EditVendorRequest $request,Vendor $vendor)
    {
        if(isset($request->name)){
            $request->merge([
                'first_name' => $request->name
            ]);
        }
        $user = User::find($vendor?->owner_id)->update($request->all());

        return (new CustomResponseResource([
            'title' => 'Details updated successfully!',
            'message' => "Details updated successfully!",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function listOa(Request $request)
    {
        $request->validate([
            'role' => 'required|in:OA,Property Manager',
        ]);
        $OwnerAssociations = OwnerAssociation::where('active', true)->where('role',$request->role)->get();

        return ListOAResource::collection($OwnerAssociations);
    }
    public function loginAsOptions(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        $user = User::where('email',$request->email)->first()?->id;
        $vendor = Vendor::where('owner_id',$user)->first()?->id;
        $oaIds = DB::table('owner_association_vendor')->where('vendor_id', $vendor)->pluck('owner_association_id');
        return OwnerAssociation::whereIn('id', $oaIds)->pluck('role','role')->unique();
    }
}
