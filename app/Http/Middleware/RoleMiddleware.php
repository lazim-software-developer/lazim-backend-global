<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check() || !auth()->user()->hasRole($role)) {
            abort(403);
        }

        app()->bind('filament.panel', function () {
            return filament()->getPanel('app');
        });

        return $next($request);
    }
}
