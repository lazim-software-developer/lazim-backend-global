<?php

namespace App\Http\Middleware;

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
        // Define the expected headers and their values
        $expectedHeaders = [
            'Content-Type' => 'application/json',
            'APIKey' => 'SDF8S7DF89S7DF9SDFSD897FDLKASD90',
            'ClientID' => '1247821',
        ];

        // Validate the headers
        foreach ($expectedHeaders as $header => $value) {
            if ($request->header($header) !== $value) {
                return response()->json(['error' => "$header is missing or invalid."], 400);
            }
        }

        return $next($request);
    }
}
