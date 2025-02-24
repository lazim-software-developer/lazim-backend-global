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
        $data['password'] = 'test1234';

        // $requestPayload =  [
        //     'from_date' => '2024-01-01', // Example start date
        //     'to_date' => '2024-12-31', // Example end date
        //     'customer' => $request->customer ?? null,
        //     'status' => $request->status ?? null,
        //     'page' => $request->page ?? 1,
        //     'per_page' => $request->per_page ?? 20,
        //     'order_by' => $request->order_by ?? 'created_at',
        //     'direction' => $request->direction ?? 'desc',
        //     'building_ids' => $building_ids, // Sending building ids as an array to the API
        // ];
        // dd($data);
        $responseLogin = AuthenticationService::authenticate($data); // api loggedin
        if ($responseLogin == null) {
            $this->throwFailureValidationException();
        }

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
