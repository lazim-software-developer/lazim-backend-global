<?php

namespace App\Observers;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Filament\Resources\NocFormResource;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\SaleNOC;
use App\Models\Master\Role;
use App\Models\Order;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->payment_status == 'succeeded') {
            $oaId = $order->orderable->owner_association_id;
            $user = User::where('role_id', Role::where('name', 'OA')->first()->id)->where('owner_association_id',$oaId)->first();
            if ($user) {
                Notification::make()
                    ->success()
                    ->title("Payment Update")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success')
                    ->body('Payment is done for ' . class_basename($order->orderable_type))
                    ->sendToDatabase($user)
                    ->actions([
                        Action::make('View')
                            ->button()
                            ->url(function ($order,$oaId){
                                if($order->orderable_type == AccessCard::class){
                                   return AccessCardFormsDocumentResource::getUrl('edit', [OwnerAssociation::where('id',$oaId)->first()?->slug,$order->id]);
                                }
                                if($order->orderable_type == FitOutForm::class){
                                   return FitOutFormsDocumentResource::getUrl('edit', [OwnerAssociation::where('id',$oaId)->first()?->slug,$order->id]);
                                }
                                if($order->orderable_type == SaleNOC::class){
                                   return NocFormResource::getUrl('edit', [OwnerAssociation::where('id',$oaId)->first()?->slug,$order->id]);
                                }
                            }),
                    ]);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
