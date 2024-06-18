<?php

namespace App\Observers;

use App\Filament\Resources\Vendor\VendorResource;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class VendorObserver
{
    /**
     * Handle the Vendor "created" event.
     */
    public function created(Vendor $vendor): void
    {
        $requiredPermissions = ['view_any_vendor::vendor'];
        $roles = Role::where('owner_association_id',$vendor->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $vendor->owner_association_id)->whereNotIn('role_id', $roles)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
            Notification::make()
            ->success()
            ->title("New Vendor")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New vendor created '.$vendor->name)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => VendorResource::getUrl('edit', [$vendor])),
            ])
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Vendor "updated" event.
     */
    public function updated(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "deleted" event.
     */
    public function deleted(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "restored" event.
     */
    public function restored(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "force deleted" event.
     */
    public function forceDeleted(Vendor $vendor): void
    {
        //
    }
}
