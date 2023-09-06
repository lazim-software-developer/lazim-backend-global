<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master\Role;
use App\Models\User\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (!auth()->attempt($credentials)) {
                return response(['message' => 'Invalid credentials'], 403);
            }

            if (in_array($user->role->name, Role::pluck('name')->toArray())) {
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
}
