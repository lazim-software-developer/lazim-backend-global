<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            
            if (empty($request->getContent())) {
                return response()->json(['error' => 'Empty Body'], 400);
            }
    
            // Manually decode JSON and check for errors
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
    
            // Generate a unique identifier for the request body
            $requestHash = md5($request->getContent());
    
            // Check if this request has been processed before
            if (Cache::has('processed_request:' . $requestHash)) {
                return response()->json(['error' => 'Duplicate Request'], 400);
            }
    
            // Store the request identifier in cache with a TTL (time-to-live) to prevent future duplicates
            Cache::put('processed_request:' . $requestHash, true, now()->addMinutes(5)); // Adjust TTL as needed

            $values = array_column($data['parameters'], 'key');
            if (count($values) !== count(array_unique($values))) {
                return response()->json(['error' => 'Duplicate Keys'], 400);
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
