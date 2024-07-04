<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MollakToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Log the mollak_id header
            Log::info($request->header('mollak_id'));

            // Check if the request method is POST
            if (!$request->isMethod('post')) {
                return response()->json(['error' => 'Method Not Allowed'], 405);
            }

            // Check if the mollak_id header is not the expected value
            if ($request->header('mollak_id') != 'TKX8z4TpH9wL') {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Proceed with the request
            return $next($request);
        } catch (\Exception $e) {
            // Log the exception
            Log::error($e->getMessage());

            // Return a JSON response for exceptions
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
}
