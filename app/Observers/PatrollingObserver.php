<?php

namespace App\Observers;

use App\Filament\Resources\PatrollingResource;
use App\Models\Gatekeeper\Patrolling;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class PatrollingObserver
{
    /**
     * Handle the Patrolling "created" event.
     */
    public function created(Patrolling $patrolling): void
    {
        $requiredPermissions = ['view_any_patrolling'];
        $roles = Role::where('owner_association_id',$patrolling->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $patrolling->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });//MAKE AUTH USER ID IN USER WHERENOT-----------
        Notification::make()
        ->success()
        ->title('New Patrolling')
        ->body('New Patrolling Details is Received')
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->actions([
            Action::make('View')
            ->button()
            ->url(fn () => PatrollingResource::getUrl('index',[OwnerAssociation::where('id',$patrolling->owner_association_id)->first()?->slug])),

        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Patrolling "updated" event.
     */
    public function updated(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "deleted" event.
     */
    public function deleted(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "restored" event.
     */
    public function restored(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "force deleted" event.
     */
    public function forceDeleted(Patrolling $patrolling): void
    {
        //
    }
}
