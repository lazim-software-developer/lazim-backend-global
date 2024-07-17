<?php

namespace App\Observers;

use App\Filament\Resources\ResidentialFormResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class ResidentialFormObserver
{
    /**
     * Handle the ResidentialForm "created" event.
     */
    public function created(ResidentialForm $residentialForm): void
    {
        $requiredPermissions = ['view_any_residential::form'];
        $roles = Role::where('owner_association_id',$residentialForm->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $residentialForm->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
        ->success()
        ->title("New ResidentialForm Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by'.auth()->user()->first_name)
        ->actions([
            Action::make('view')
                ->button()
                ->url(fn () => ResidentialFormResource::getUrl('edit', [OwnerAssociation::where('id',$residentialForm->owner_association_id)->first()?->slug,$residentialForm->id])),
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the ResidentialForm "updated" event.
     */
    public function updated(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "deleted" event.
     */
    public function deleted(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "restored" event.
     */
    public function restored(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "force deleted" event.
     */
    public function forceDeleted(ResidentialForm $residentialForm): void
    {
        //
    }
}
