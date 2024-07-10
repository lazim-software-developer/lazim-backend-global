<?php

namespace App\Observers;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        // $tenant = Filament::getTenant();
        // $slug = $tenant->slug;
        $slug = DB::table('owner_associations')->where('id',$item->owner_association_id)->value('slug');

        $requiredPermissions = ['view_any_item'];
        $roles = Role::where('owner_association_id',$item->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $item->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', 1)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });//MAKE AUTH USER ID IN USER WHERENOT-----------
        Notification::make()
        ->success()
        ->title('Item Updated')
        ->body('New Item Update Received')
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->actions([
            Action::make('View')
            ->button()
            ->url(url($slug . ItemResource::getUrl('view', [$item->id])))
            ])
        ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}

