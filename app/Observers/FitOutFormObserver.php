<?php

namespace App\Observers;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class FitOutFormObserver
{
    /**
     * Handle the FitOutForm "created" event.
     */
    public function created(FitOutForm $fitOutForm): void
    {
        $requiredPermissions = ['view_any_master::facility'];
        $notifyTo = User::where('owner_association_id', $fitOutForm->owner_association_id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
        ->success()
        ->title("New FitOut Form Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by '.auth()->user()->first_name)
        ->actions([
            Action::make('view')
                ->button()
                ->url(fn () => FitOutFormsDocumentResource::getUrl('edit', [$fitOutForm])),
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the FitOutForm "updated" event.
     */
    public function updated(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "deleted" event.
     */
    public function deleted(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "restored" event.
     */
    public function restored(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "force deleted" event.
     */
    public function forceDeleted(FitOutForm $fitOutForm): void
    {
        //
    }
}
