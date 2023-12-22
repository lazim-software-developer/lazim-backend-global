<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GateKeeperLoginRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Login route for OA user
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($credentials)) {
            return response(['message' => 'Invalid credentials'], 403);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $allowedRoles = ['OA','Vendor', 'Technician','Security'];

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if the user's email and phone number is verified

        if (!$user->email_verified) {
            return (new CustomResponseResource([
                'title' => 'Email Verification Required',
                'message' => 'Email is not verified.',
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        if (!$user->phone_verified) {
            return (new CustomResponseResource([
                'title' => 'Phone Verification Required',
                'message' => 'Phone number is not verified.',
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        if ($user) {
            if (in_array($user->role->name, $allowedRoles)) {
                if ($user->active == 1) {
                    if ($user->tokens()) {
                        $count = $user->tokens()
                            ->where(['tokenable_type' => 'user', 'tokenable_id' => $user->id])->count();
                        if ($count > 0) {
                            $user->tokens()
                                ->where(['tokenable_type' => 'user', 'tokenable_id' => $user->id])->delete();
                        }
                        $token = $user->createToken($user->role->name)->plainTextToken;
                        $user->profile_photo = $user->profile_photo ? Storage::disk('s3')->url($user->profile_photo) : null;
                        return response(['token' => $token, 'user' => $user], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'Your account is deactivated. Please contact admin for more information',
                    ]);
                }
            } else {
                return response(['message' => "You are not authorized to login!"], 403);
            }
        } else {
            return response(['message' => 'User not found!'], 403);
        }
    }

    /**
     * Login a user based on email, password, and role for customer and vendors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $request->email      The email of the user trying to log in.
     * @param  string  $request->password   The password of the user trying to log in.
     * @param  string  $request->role       The role of the user (either 'Owner' or 'Tenant').
     *
     * @return \Illuminate\Http\JsonResponse
     * @return 200  array  ['token' => $token, 'refresh_token' => $refreshToken]  On successful login, returns a JSON with the access and refresh tokens.
     * @return 422  array  ['email' => ['The provided credentials are incorrect.']]  On validation error or incorrect credentials.
     */
    public function customerLogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // if (!$user || !Hash::check($request->password, $user->password) || $user->role->name !== $request->role) {
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if the user's email and phone number is verified

        if (!$user->email_verified) {
            return (new CustomResponseResource([
                'title' => 'Email Verification Required',
                'message' => 'Email is not verified.',
                'code' => 403,
                'data' => $user
            ]))->response()->setStatusCode(403);
        }

        if (!$user->phone_verified) {
            return (new CustomResponseResource([
                'title' => 'Phone Verification Required',
                'message' => 'Phone number is not verified.',
                'code' => 403,
                'data' => $user
            ]))->response()->setStatusCode(403);
        }

        // Create a new access token
        $token = $user->createToken($request->role)->plainTextToken;

        // Create a refresh token and store it in the database (you can use a separate table for this)
        $refreshToken = Str::random(40);
        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(30)  // Set the expiration time for the refresh token
        ]);

        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user
        ], 200);
    }

    // Refresh token 
    public function refreshToken(Request $request)
    {
        $validatedData = $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $storedToken = DB::table('refresh_tokens')
            ->where('token', hash('sha256', $validatedData['refresh_token']))
            ->first();

        if (!$storedToken || Carbon::parse($storedToken->expires_at)->isPast()) {
            return response()->json(['message' => 'Invalid or expired refresh token.'], 400);
        }

        $user = User::find($storedToken->user_id);
        $newToken = $user->createToken('access-token');

        // Optionally, you can delete the used refresh token and generate a new one

        return response()->json([
            'access_token' => $newToken->plainTextToken
        ]);
    }

    public function setPassword(SetPasswordRequest $request)
    {
        // Fetch the user by email
        $user = User::where('email', $request->email)->first();

        // Set the new password
        $user->password = Hash::make($request->password);
        $user->save();

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Password set successfully!',
            'code' => 200,
            'status' => 'success'
        ]))->response()->setStatusCode(200);
    }

    public function expo(Request $request)
    {
        if ($request->has('status') && $request->status == 'login') {
            $expo = ExpoPushNotification::where('token', $request->token)->first();

            if (!$expo) {
                ExpoPushNotification::create([
                    'user_id' => auth()->user()->id,
                    'token'   => $request->token,
                ]);

                return response()->json([
                    'message' => 'Token saved successfully.',
                ]);
            }
        }

        if ($request->has('status') && $request->status == 'logout') {
            ExpoPushNotification::where('token', $request->token)->delete();

            return response()->json([
                'message' => 'Token deleted successfully.',
            ]);
        }
    }

    // Gatekeeper login 
    public function gateKeeperLogin(GateKeeperLoginRequest $request) {
        $user = User::where('email', $request->email)->first();

        // if (!$user || !Hash::check($request->password, $user->password) || $user->role->name !== $request->role) {
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if the user's email and phone number is verified
        if (!$user->email_verified) {
            return (new CustomResponseResource([
                'title' => 'Email Verification Required',
                'message' => 'Email is not verified.',
                'code' => 403,
                'data' => $user
            ]))->response()->setStatusCode(403);
        }

        if (!$user->phone_verified) {
            return (new CustomResponseResource([
                'title' => 'Phone Verification Required',
                'message' => 'Phone number is not verified.',
                'code' => 403,
                'data' => $user
            ]))->response()->setStatusCode(403);
        }

        // Check if the gatekeeper is having active account inuildingPOC table
        $building = BuildingPoc::where([
            'user_id' => $user->id,
            'role_name' => 'security',
            'active' => 1
        ]);

        if(!$building->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "You don't have access to login to the application!",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        // Create a new access token
        $token = $user->createToken($user->role->name)->plainTextToken;

        // Create a refresh token and store it in the database (you can use a separate table for this)
        $refreshToken = Str::random(40);
        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(30)  // Set the expiration time for the refresh token
        ]);

        $user->building_id = $building->first()->building_id;
        $user->building_name = $building->first()->building->name;
        $user->slug = $building->first()->building->slug;
        $user->profile_photo = $user->profile_photo ? Storage::disk('s3')->url($user->profile_photo) : null;

        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user
        ], 200);
    }
}
