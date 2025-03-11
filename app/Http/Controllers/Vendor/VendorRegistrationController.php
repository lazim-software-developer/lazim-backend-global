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

            if ($vendor) {
                $isAttached = $vendor->ownerAssociation()->wherePivot('owner_association_id', $request->owner_association_id)->exists();
                $ownerAssociation = OwnerAssociation::where('id', $request->owner_association_id)->first();
                if ($isAttached) {
                    return (new CustomResponseResource([
                        'title' => 'vendor_already_exists',
                        'message' => "This vendor is already registered with this " . $ownerAssociation?->role ?? 'Association' . '.',
                        'code' => 403, // Conflict status code
                        'data' => $vendor,
                    ]))->response()->setStatusCode(403);
                }
                $type = $userData->first()?->role->name;

                $vendor->ownerAssociation()->attach($request->owner_association_id, ['from' => now()->toDateString(), 'active' => false, 'type' => $request->role]);
                return (new CustomResponseResource([
                    'title' => 'vendor_exists',
                    'message' => "You have successfully registered with the new " . $ownerAssociation?->role ?? 'Association' . ". They will get back to you soon!",
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
        if ($request->has('role') && isset($request->role) && $request->role === 'Property Manager') {
            $role = Role::where('name', 'Facility Manager')->value('id');
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
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }
        $user = User::find($request->owner_id);

        // Pehle check karenge ki vendor exist karta hai ya nahi
        $vendor = Vendor::where('owner_id', $request->owner_id)->first();

        if ($vendor) {
            // Agar vendor exist karta hai toh usko update karenge
            $vendor->update([
                'name' => optional($user)->first_name ?? $request->name,
                'tl_number' => $request->tl_number,
                'landline_number' => $request->landline_number,
                'owner_id' => $request->owner_id,
                'address_line_1' => $request->address_line_1,
                'website' => $request->website,
                'tl_expiry' => $request->tl_expiry,
                'risk_policy_expiry' => $request->risk_policy_expiry,
                'owner_association_id' => $request->owner_association_id,
            ]);

            $message = "Company details successfully updated!";
        } else {
            // Agar vendor exist nahi karta toh naye record create karenge
            $vendor = Vendor::create([
                'owner_id' => $request->owner_id,
                'tl_number' => $request->tl_number,
                'landline_number' => $request->landline_number,
                'name' => optional($user)->first_name ?? $request->name,
                'address_line_1' => $request->address_line_1,
                'website' => $request->website,
                'tl_expiry' => $request->tl_expiry,
                'risk_policy_expiry' => $request->risk_policy_expiry,
                'owner_association_id' => $request->owner_association_id,
            ]);

            $message = "Company details successfully created!";
        }
        $type = OwnerAssociation::where('id', $request->owner_association_id)->first()?->role;

        $user->ownerAssociation()->syncWithoutDetaching([
            $request->owner_association_id => ['from' => now()->toDateString()]
        ]);

        $vendor->ownerAssociation()->syncWithoutDetaching([
            $request->owner_association_id => ['from' => now()->toDateString(), 'type' => $type]
        ]);

        $doc = Document::create([
            "name" => "risk_policy",
            "document_library_id" => DocumentLibrary::where('name', 'Risk policy')->first()->id,
            "owner_association_id" => $request->owner_association_id,
            "status" => 'pending',
            "documentable_id" => $vendor->id,
            "expiry_date" => $request->risk_policy_expiry,
            "documentable_type" => Vendor::class,
        ]);
        Document::create([
            "name" => "tl_document",
            "document_library_id" => DocumentLibrary::where('name', 'TL document')->first()->id,
            "owner_association_id" => $request->owner_association_id,
            "status" => 'pending',
            "documentable_id" => $vendor->id,
            "expiry_date" => $request->tl_expiry,
            "documentable_type" => Vendor::class,
        ]);

        return (new CustomResponseResource([
            'title' => $message,
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $vendor,
        ]))->response()->setStatusCode(201);
    }

    public function managerDetails(ManagerDetailsRequest $request, $vendorId)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
            // $vendor = Vendor::find($vendorId);
        }
        // Find the vendor
        $vendor = Vendor::find($vendorId);

        if (!$vendor) {
            return (new CustomResponseResource([
                'message' => 'Invalid vendor ID provided.',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        $request->merge(['vendor_id' => $vendor->id]);

        // Pehle se existing manager ko delete karna
        VendorManager::where('vendor_id', $vendor->id)->delete();

        // Naya vendor manager create karna
        $manager = VendorManager::create($request->all());

        return (new CustomResponseResource([
            'title' => "Vendor Manager Details entered successfully!",
            'message' => "",
            'code' => 201,
            'status' => 'success',
            'data' => $manager,
        ]))->response()->setStatusCode(201);
    }

    public function updateManagerDetails(ManagerDetailsRequest $request, Vendor $vendor)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

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
        if ($managerId) {
            $manager = VendorManager::find($managerId)->update($request->all());
        } else {
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

    public function editVendorDetails(EditVendorRequest $request, Vendor $vendor)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }
        if (isset($request->name)) {
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
        $OwnerAssociations = OwnerAssociation::where('active', true)->where('role', $request->role)->get();

        return ListOAResource::collection($OwnerAssociations);
    }
    public function loginAsOptions(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        $user = User::where('email', $request->email)->first()?->id;
        $vendor = Vendor::where('owner_id', $user)->first()?->id;
        $oaIds = DB::table('owner_association_vendor')->where('vendor_id', $vendor)->pluck('owner_association_id');
        return OwnerAssociation::whereIn('id', $oaIds)->pluck('role', 'role')->unique();
    }
    public function registeredWith(Request $request)
    {
        $user = auth()->user();
        $vendor = Vendor::where('owner_id', $user->id)->first()?->id;
        $oaIds = DB::table('owner_association_vendor')
            ->where(['vendor_id' => $vendor, 'active' => true, 'status' => 'approved'])
            ->pluck('owner_association_id');
        $reUploadDocuments = DB::table('owner_association_vendor')
            ->where(['vendor_id' => $vendor, 'status' => 'rejected'])
            ->exists();
        return [
            'registered_with' => OwnerAssociation::whereIn('id', $oaIds)->pluck('role', 'role')->unique(),
            're_upload_documents' => $reUploadDocuments ? true : false,
            'vendor_id' => $vendor,
        ];
    }
}
