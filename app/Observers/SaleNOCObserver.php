<?php

namespace App\Observers;

use App\Filament\Resources\NocFormResource;
use App\Models\Building\Building;
use App\Models\Forms\SaleNOC;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;


class SaleNOCObserver
{
    /**
     * Handle the SaleNOC "created" event.
     */
    public function created(SaleNOC $saleNOC): void
    {
        $requiredPermissions = ['view_any_noc::form'];
        $notifyTo = User::where('owner_association_id', $saleNOC->owner_association_id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });;
        Notification::make()
        ->success()
        ->title("New SaleNoc Submission")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body('New form submission by '.auth()->user()->first_name)
        ->actions([
            Action::make('view')
                ->button()
                ->url(fn () => NocFormResource::getUrl('edit', [$saleNOC])),
        ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the SaleNOC "updated" event.
     */
    public function updated(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "deleted" event.
     */
    public function deleted(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "restored" event.
     */
    public function restored(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "force deleted" event.
     */
    public function forceDeleted(SaleNOC $saleNOC): void
    {
        //
    }
}
