<?php

namespace App\Http\Middleware\Custom;

use App\Http\Resources\CustomResponseResource;
use App\Models\Building\BuildingPoc;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveGateKeeper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = BuildingPoc::where([
            'user_id' => auth()->user()->id,
            'role_name' => 'security',
            'active' => 1
        ]);

        if(!$account->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "You don't have any active building associated with you",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        return $next($request);
    }
}
