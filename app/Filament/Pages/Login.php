<?php

namespace App\Filament\Pages;

use App\Models\OwnerAssociation;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as AuthLogin;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends AuthLogin
{

    public function authenticate(): ?LoginResponse
    {
        $request = request(); // Get the current request instance

        // Retrieve and log the full URL
        $fullUrl = $request->fullUrl();

        // Extract the host from the URL
        $host = parse_url($fullUrl, PHP_URL_HOST);

        // Extract the subdomain
        $subdomain = $this->getSubdomain($host);

        if (!$subdomain) {
            Filament::auth()->logout(); 
            throw ValidationException::withMessages([
                'data.email' => __('Subdomain is required.'),
            ]);
        }

        $ownerAssociation = OwnerAssociation::where('slug', $subdomain)->first();
        if (!$ownerAssociation) {
            Filament::auth()->logout();
            throw ValidationException::withMessages([
                'data.email' => __('Tenant not found.'),
            ]);
        }


        // Bind the tenant to the application with a unique key
        app()->singleton('currentOwnerAssociation', function () use ($ownerAssociation) {
            return $ownerAssociation;
        });


        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        $user = auth()->user();
        $ownerAssociation = app('currentOwnerAssociation');
        Log::info("User ID: {$user->id}, Owner Association ID: {$user->owner_association_id}");

        if ($user->owner_association_id !== $ownerAssociation->id) {
            Filament::auth()->logout();
            throw ValidationException::withMessages([
                'data.email' => __('Unauthorized access.'),
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getSubdomain($host)
    {
        $parts = explode('.', $host);
        if (count($parts) == 2) {
            // Assume the subdomain is the first part of the host
            return $parts[0];
        }
        return null;
    }
}
