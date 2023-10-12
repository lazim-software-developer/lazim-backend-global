<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Http\Resources\CustomResponseResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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

        $allowedRoles = ['OA'];

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
     * @param  string  $request->role       The role of the user (either 'residents' or 'vendors').
     *
     * @return \Illuminate\Http\JsonResponse
     * @return 200  array  ['token' => $token, 'refresh_token' => $refreshToken]  On successful login, returns a JSON with the access and refresh tokens.
     * @return 422  array  ['email' => ['The provided credentials are incorrect.']]  On validation error or incorrect credentials.
     */
    public function customerLogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $user->role->name !== $request->role) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
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
            'refresh_token' => $refreshToken
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
            'title' => 'Password set successfully!',
            'message' => 'Test',
            'errorCode' => 200, 
        ]))->response()->setStatusCode(200);
    }
}
