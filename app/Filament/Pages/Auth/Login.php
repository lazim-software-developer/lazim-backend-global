<?php

namespace App\Filament\Pages\Auth;

use App\Services\AuthenticationService;
use Filament\Pages\Auth\Login as BaseAuth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;



class Login extends BaseAuth
{
    public function authenticate(): ?LoginResponse
    {


        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }
        $data = $this->form->getState();
        
        // if(isset($data['password']) && empty($data['password'])) {
        //     $data['password'] = 'test1234';
        // }
        // $responseLogin = AuthenticationService::authenticate($data); // api loggedin
        // if ($responseLogin == null) {
        //     $this->throwFailureValidationException();
        // }

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
