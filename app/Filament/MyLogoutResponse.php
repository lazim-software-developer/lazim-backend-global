<?php

namespace App\Filament;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class MyLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $panel = filament()->getId();
        if ($panel === 'app') {
            Filament::auth()->logout();
            return redirect()->to(env('APP_URL') . '/app/login');
        }
        return redirect('/admin');
    }
}
