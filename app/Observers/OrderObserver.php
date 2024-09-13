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

            $baseClass = class_basename($order->orderable_type);
            $oaId = null;
            $link = '';

            if ($order->orderable_type == AccessCard::class) {
                $oaId = AccessCard::where('id', $order->id)->first()?->owner_association_id;
                $link = AccessCardFormsDocumentResource::getUrl('edit', [OwnerAssociation::where('id', $oaId)->first()?->slug, $order->id]);
            }
            if ($order->orderable_type == FitOutForm::class) {
                $oaId = FitOutForm::where('id', $order->id)->first()?->owner_association_id;
                $link = FitOutFormsDocumentResource::getUrl('edit', [OwnerAssociation::where('id', $oaId)->first()?->slug, $order->id]);
            }
            if ($order->orderable_type == SaleNOC::class) {
                $oaId = SaleNOC::where('id', $order->id)->first()?->owner_association_id;
                $link = NocFormResource::getUrl('edit', [OwnerAssociation::where('id', $oaId)->first()?->slug, $order->id]);
            }

            $user = User::where('role_id', Role::where('name', 'OA')->where('owner_association_id',$oaId)->first()->id)
            ->where('owner_association_id',$oaId)->get();
            if ($user) {
                Notification::make()
                    ->success()
                    ->title("Payment Update")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success')
                    ->actions([
                        Action::make('View')
                            ->button()
                            ->url(fn () => $link),
                    ])
                    ->body('Payment is done for ' . class_basename($order->orderable_type))
                    ->sendToDatabase($user);
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
