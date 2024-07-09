<?php

namespace App\Http\Middleware;

use App\Models\AuthCredential;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTallyApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {

           // Define the expected headers and their values
            $clientId = $request->header('ClientID');
            $apiKey = $request->header('APIKey');

            $apiRecord = AuthCredential::where('client_id', $clientId)
                ->where('api_key', $apiKey)
                ->where('module', AuthCredential::TALLY_MODULE)
                ->first();

            if (!$apiRecord) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return $next($request);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
