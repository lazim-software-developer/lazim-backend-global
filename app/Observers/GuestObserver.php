<?php

namespace App\Observers;

use App\Filament\Resources\GuestRegistrationResource;
use App\Models\Building\Building;
use App\Models\Forms\Guest;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;


class GuestObserver
{
    /**
     * Handle the Guest "created" event.
     */
    public function created(Guest $guest): void
    {
        $requiredPermissions = ['view_any_guest::registration'];
        $notifyTo = User::where('owner_association_id', $guest->owner_association_id)->whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
        ->success()
        ->title("New Guest registration form Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by '.auth()->user()->first_name)
        ->actions([
            Action::make('view')
                ->button()
                ->url(fn () => GuestRegistrationResource::getUrl('edit', [$guest])),
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Guest "updated" event.
     */
    public function updated(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "deleted" event.
     */
    public function deleted(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "restored" event.
     */
    public function restored(Guest $guest): void
    {
        //
    }

    /**
     * Handle the Guest "force deleted" event.
     */
    public function forceDeleted(Guest $guest): void
    {
        //
    }
}
