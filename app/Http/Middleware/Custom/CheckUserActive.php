<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Http\Resources\CustomResponseResource;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // If there's an authenticated user and their account is not active
        $inactive = false;
        if ($user && isset($user->active) && !$user->active) {
           $inactive = true;
        }

        // If user is not authenticated (e.g., during a login attempt)
        if (!$user) {
            $email = $request->input('email');
            $userFromDb = User::where('email', $email)->first();

            if ($userFromDb && !$userFromDb->active) {
                $inactive = true;
            }
        }
        
        // If inactive, return error 
        if($inactive) {
            return (new CustomResponseResource([
                'title' => 'Account Status',
                'message' => 'Please wait while we verify your details. You’ll receive a confirmation soon.',
                'code' => 400, 
            ]))->response()->setStatusCode(400);
        }

        return $next($request);
    }
}
