<?php

namespace App\Observers;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
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

        $requiredPermissions = ['view_any_item'];
        $oa_ids = DB::table('building_owner_association')
            ->where(['building_id' => $item->building_id,'active'=> true])
            ->pluck('owner_association_id');
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        foreach($oa_ids as $oa_id){
            $notifyTo = User::where('owner_association_id', $oa_id)->whereNotIn('role_id', $roles)
                ->whereNot('id', auth()->user()?->id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            if($notifyTo->count() > 0){
                foreach($notifyTo as $user){
                    if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->item_id', $item->id)->exists()){
                        $data=[];
                        $data['notifiable_type']='App\Models\User\User';
                        $data['notifiable_id']=$user->id;
                        $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
                        $data['url']=ItemResource::getUrl('view', [$slug,$item->id]);
                        $data['title']="Item Updated for Building:".$item->building->name;
                        $data['body']='New Item Update Received';
                        $data['building_id']=$item->building_id;
                        $data['custom_json_data']=json_encode([
                            'building_id' => $item->building_id,
                            'item_id' => $item->id,
                            'user_id' => auth()->user()->id ?? null,
                            'owner_association_id' => $oa_id,
                            'type' => 'Item',
                            'priority' => 'Medium',
                        ]);
                        NotificationTable($data);
                    }
                }
            }
            // Notification::make()
            // ->success()
            // ->title('Item Updated')
            // ->body('New Item Update Received')
            // ->icon('heroicon-o-document-text')
            // ->iconColor('warning')
            // ->actions([
            //     Action::make('View')
            //     ->button()
            //     ->url(function() use ($item,$oa_id){
            //         $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
            //         if($slug){
            //             return ItemResource::getUrl('view', [$slug,$item->id]);
            //         }
            //         return url('/app/items/' . $item->id);
            //     }),
            // ])
            // ->sendToDatabase($notifyTo);
        }
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
