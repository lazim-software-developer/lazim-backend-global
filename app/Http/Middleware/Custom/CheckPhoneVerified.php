<?php

namespace App\Http\Middleware\Custom;

use App\Http\Resources\CustomResponseResource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user() || !auth()->user()->phone_verified) {
            return (new CustomResponseResource([
                'title' => 'Phone Verification Required',
                'message' => 'Phone number is not verified.',
                'code' => 403,
            ]))->response()->setStatusCode(403); // Forbidden
        }

        return $next($request);
    }
}
