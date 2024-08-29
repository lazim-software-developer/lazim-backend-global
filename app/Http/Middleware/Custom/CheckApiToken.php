<?php

namespace App\Http\Middleware\Custom;

use App\Http\Resources\CustomResponseResource;
use Closure;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('API-TOKEN');

        if (!$token || $token !== env('API_ACCESS_TOKEN')) {
            return (new CustomResponseResource([
                'title' => 'Unauthorized',
                'message' => 'You are unauthorized to access this API',
                'code' => 403, 
            ]))->response()->setStatusCode(401);
        }

        return $next($request);
    }
}
