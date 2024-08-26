<?php

namespace App\Observers;

use App\Jobs\FetchBuildingsJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class OwnerAssociationObserver
{
    public function created(OwnerAssociation $ownerAssociation)
    {
        // FetchBuildingsJob::dispatch($ownerAssociation);
        $notifyTo = User::where('role_id',Role::where('name','Admin')->first()->id)->get();
        Notification::make()
            ->success()
            ->title("New Owner Association")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New Owner Association Added')
            // ->actions([
            //     Action::make('view')
            //         ->button()
            //         ->url(fn () => OwnerAssociation::getUrl('edit', [$ownerAssociation->id])),
            // ])
            ->sendToDatabase($notifyTo);
    }
}
