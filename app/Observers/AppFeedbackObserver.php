<?php

namespace App\Observers;

use App\Filament\Resources\AppFeedbackResource;
use App\Models\AppFeedback;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class AppFeedbackObserver
{
    /**
     * Handle the AppFeedback "created" event.
     */
    public function created(AppFeedback $appFeedback): void
    {
        $requiredPermissions = ['view_any_app::feedback'];
        $role_id = Role::where('name','Admin')->pluck('id');
        $notifyTo = User::whereIn('role_id', $role_id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
        ->success()
        ->title('App Feedback')
        ->body('New App Feedback Received')
        ->icon('heroicon-s-pencil-square')
        ->actions([
            Action::make('View')
            ->button()
            ->url( fn () => AppFeedbackResource::getUrl('view',[$appFeedback->id])),
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the AppFeedback "updated" event.
     */
    public function updated(AppFeedback $appFeedback): void
    {
        //
    }

    /**
     * Handle the AppFeedback "deleted" event.
     */
    public function deleted(AppFeedback $appFeedback): void
    {
        //
    }

    /**
     * Handle the AppFeedback "restored" event.
     */
    public function restored(AppFeedback $appFeedback): void
    {
        //
    }

    /**
     * Handle the AppFeedback "force deleted" event.
     */
    public function forceDeleted(AppFeedback $appFeedback): void
    {
        //
    }
}
