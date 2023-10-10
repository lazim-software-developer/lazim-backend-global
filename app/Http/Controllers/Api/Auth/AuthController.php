<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Validation\ValidationException;

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
     * Login a user based on email, password, and role for custoemr and vendors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $request->email      The email of the user trying to log in.
     * @param  string  $request->password   The password of the user trying to log in.
     * @param  string  $request->role       The role of the user (either 'residents' or 'vendors').
     *
     * @return \Illuminate\Http\JsonResponse
     * @return 200  array  ['token' => $token]  On successful login, returns a JSON with the access token.
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

        $token = $user->createToken($request->role)->plainTextToken;

        return response()->json(['token' => $token], 200);
    }
}
