<?php

namespace App\Observers;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\AccessCard;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class AccessCardObserver
{
    /**
     * Handle the AccessCard "created" event.
     */
    public function created(AccessCard $accessCard): void
    {
        $requiredPermissions = ['view_any_access::card::forms::document'];
        $roles = Role::where('owner_association_id',$accessCard->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $accessCard->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
            Notification::make()
                ->success()
                ->title("New Access Card Submission")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New form submission by ' . auth()->user()->first_name)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function() use ($accessCard){
                            $slug = OwnerAssociation::where('id',$accessCard->owner_association_id)->first()?->slug;
                            if($slug){
                                return AccessCardFormsDocumentResource::getUrl('edit', [$slug,$accessCard?->id]);
                            }
                            return url('/app/access-card-forms-documents/' . $accessCard?->id.'/edit');
                        }),
                ])
                ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the AccessCard "updated" event.
     */
    public function updated(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "deleted" event.
     */
    public function deleted(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "restored" event.
     */
    public function restored(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "force deleted" event.
     */
    public function forceDeleted(AccessCard $accessCard): void
    {
        //
    }
}
