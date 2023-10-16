<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\CustomResponseResource;
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
    
        return (new CustomResponseResource([
            'title' => 'Registration successful!',
            'message' => "We've sent verification code to your email Id and phone. Please verify to continue using the application",
            'errorCode' => 201, 
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }
    
    
}
