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
use Illuminate\Support\Facades\DB;

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

            if ($order->orderable_type == AccessCard::class) {
                $accessCard = AccessCard::where('id', $order->orderable_id)->first();
                $oaIds = DB::table('building_owner_association')
                    ->where('building_id', $accessCard?->building_id)
                    ->pluck('owner_association_id');
                $pm = OwnerAssociation::whereIn('id', $oaIds)->where('role', 'Property Manager')->first();

                foreach($oaIds as $oaId){
                    $oa         = OwnerAssociation::find($oaId);
                    $flatexists = DB::table('property_manager_flats')
                        ->where(['flat_id' => $accessCard->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                        ->exists();
                    if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                        $user = User::whereIn('role_id', Role::whereIn('name', ['OA', 'Property Manager'])
                            ->where('owner_association_id', $oaId)->pluck('id'))
                            ->where('owner_association_id', $oaId)->get();
                        $slug = OwnerAssociation::where('id', $oaId)->first()?->slug;
                        $link = $slug
                        ? AccessCardFormsDocumentResource::getUrl('edit', [$slug, $order->orderable_id])
                        : url('/app/access-card-forms-documents/' . $order->orderable_id . '/edit');

                        if ($user) {
                            Notification::make()
                                ->success()
                                ->title("Payment Update")
                                ->icon('heroicon-o-document-text')
                                ->iconColor('success')
                                ->actions([
                                    Action::make('View')
                                        ->button()
                                        ->url(fn() => $link),
                                ])
                                ->body('Payment is done for ' . class_basename($order->orderable_type))
                                ->sendToDatabase($user);
                        }
                    }
                }
            }
            if ($order->orderable_type == FitOutForm::class) {
                $buildingId = FitOutForm::where('id', $order->orderable_id)->first()?->building_id;
                $oaIds = DB::table('building_owner_association')->where('building_id', $buildingId)->pluck('owner_association_id');
                $pm = OwnerAssociation::whereIn('id', $oaIds)->where('role', 'Property Manager')->first();

                foreach ($oaIds as $oaId) {
                    $oa         = OwnerAssociation::find($oaId);
                    $flatexists = DB::table('property_manager_flats')
                        ->where(['flat_id' => $accessCard->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                        ->exists();
                    if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                        $user = User::whereIn('role_id', Role::whereIn('name', ['OA', 'Property Manager'])
                                ->where('owner_association_id', $oaId)->pluck('id'))
                                ->where('owner_association_id', $oaId)->get();
                        $slug = OwnerAssociation::where('id', $oaId)->first()?->slug;
                        $link = $slug
                        ? FitOutFormsDocumentResource::getUrl('edit', [$slug, $order->orderable_id])
                        : url('/app/fit-out-forms-documents/' . $order->orderable_id . '/edit');

                        if ($user) {
                            Notification::make()
                                ->success()
                                ->title("Payment Update")
                                ->icon('heroicon-o-document-text')
                                ->iconColor('success')
                                ->actions([
                                    Action::make('View')
                                        ->button()
                                        ->url(fn() => $link),
                                ])
                                ->body('Payment is done for ' . class_basename($order->orderable_type))
                                ->sendToDatabase($user);
                        }
                    }
                }
            }
            if ($order->orderable_type == SaleNOC::class) {
                $buildingId = SaleNOC::where('id', $order->orderable_id)->first()?->building_id;
                $oaIds = DB::table('building_owner_association')->where('building_id', $buildingId)->pluck('owner_association_id');
                $pm = OwnerAssociation::whereIn('id', $oaIds)->where('role', 'Property Manager')->first();

                foreach ($oaIds as $oaId) {
                    $oa         = OwnerAssociation::find($oaId);
                    $flatexists = DB::table('property_manager_flats')
                        ->where(['flat_id' => $accessCard->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                        ->exists();
                    if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                        $user = User::whereIn('role_id', Role::whereIn('name', ['OA', 'Property Manager'])
                                ->where('owner_association_id', $oaId)->pluck('id'))
                                ->where('owner_association_id', $oaId)->get();
                        $slug = OwnerAssociation::where('id', $oaId)->first()?->slug;
                        $link = $slug
                        ? NocFormResource::getUrl('edit', [$slug, $order->orderable_id])
                        : url('/app/noc-forms/' . $order->orderable_id . '/edit');

                        if ($user) {
                            Notification::make()
                                ->success()
                                ->title("Payment Update")
                                ->icon('heroicon-o-document-text')
                                ->iconColor('success')
                                ->actions([
                                    Action::make('View')
                                        ->button()
                                        ->url(fn() => $link),
                                ])
                                ->body('Payment is done for ' . class_basename($order->orderable_type))
                                ->sendToDatabase($user);
                        }
                    }
                }
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
