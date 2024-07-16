<?php

namespace App\Observers;

use App\Filament\Resources\PollResource;
use App\Models\Community\PollResponse;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class PollResponseObserver
{
    /**
     * Handle the PollResponse "created" event.
     */
    public function created(PollResponse $pollResponse): void
    {
        $requiredPermissions = ['view_any_poll'];
        $roles = Role::where('owner_association_id',$pollResponse->poll->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $pollResponse->poll->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
        ->success()
        ->title('New Poll Response')
        ->body('New Poll Response Received')
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->actions([
            Action::make('View')
            ->button()
            ->url(fn () => PollResource::getUrl('edit', ['record',$pollResponse->poll_id]))
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the PollResponse "updated" event.
     */
    public function updated(PollResponse $pollResponse): void
    {
        //
    }

    /**
     * Handle the PollResponse "deleted" event.
     */
    public function deleted(PollResponse $pollResponse): void
    {
        //
    }

    /**
     * Handle the PollResponse "restored" event.
     */
    public function restored(PollResponse $pollResponse): void
    {
        //
    }

    /**
     * Handle the PollResponse "force deleted" event.
     */
    public function forceDeleted(PollResponse $pollResponse): void
    {
        //
    }
}
