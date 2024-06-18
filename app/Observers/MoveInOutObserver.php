<?php

namespace App\Observers;

use App\Filament\Resources\MoveInFormsDocumentResource;
use App\Filament\Resources\MoveOutFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\MoveInOut;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;


class MoveInOutObserver
{
    /**
     * Handle the MoveInOut "created" event.
     */
    public function created(MoveInOut $moveInOut): void
    {
        $roles = Role::where('owner_association_id',$moveInOut->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $moveInOut->owner_association_id)->whereNotIn('role_id', $roles)->get();
        if($moveInOut->type == 'move-in'){
            $requiredPermissions = ['view_any_move::in::forms::document'];
            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            Notification::make()
            ->success()
            ->title("New MoveIn Submission")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New form submission by '.auth()->user()->first_name)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => MoveInFormsDocumentResource::getUrl('edit', [$moveInOut])),
            ])
            ->sendToDatabase($notifyTo);
        }
        else{
            $requiredPermissions = ['view_any_move::out::forms::document'];
            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            Notification::make()
            ->success()
            ->title("New MoveOut Submission")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New form submission by '.auth()->user()->first_name)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => MoveOutFormsDocumentResource::getUrl('edit', [$moveInOut])),
            ])
            ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the MoveInOut "updated" event.
     */
    public function updated(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "deleted" event.
     */
    public function deleted(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "restored" event.
     */
    public function restored(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "force deleted" event.
     */
    public function forceDeleted(MoveInOut $moveInOut): void
    {
        //
    }
}
