<?php

namespace App\Observers;

use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\Vendor\VendorResource;

class VendorObserver
{
    /**
     * Handle the Vendor "created" event.
     */
    public function created(Vendor $vendor): void
    {
        $requiredPermissions = ['view_any_vendor::vendor'];
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $vendor->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        $slug = OwnerAssociation::where('id',$vendor->owner_association_id)->first()?->slug;
        if($notifyTo->count() > 0){
            foreach($notifyTo as $user){
                if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->vendor_id', $vendor->id)->exists()){
                    $data=[];
                    $data['notifiable_type']='App\Models\User\User';
                    $data['notifiable_id']=$user->id;
                    $data['url']=VendorResource::getUrl('edit', [$slug,$vendor->id]);
                    $data['title']="New Vendor";
                    $data['body']='New vendor created '.$vendor->name;
                    $data['building_id']=$vendor->building_id ?? null;
                    $data['custom_json_data']=json_encode([
                        'building_id' => $vendor->building_id ?? null,
                        'vendor_id' => $vendor->id,
                        'user_id' => $vendor->owner_id ?? null,
                        'owner_association_id' => $vendor->owner_association_id,
                        'type' => 'Vendor',
                        'priority' => 'Medium',
                    ]);
                    NotificationTable($data);
                }
            }
        }
            // Notification::make()
            // ->success()
            // ->title("New Vendor")
            // ->icon('heroicon-o-document-text')
            // ->iconColor('warning')
            // ->body('New vendor created '.$vendor->name)
            // ->actions([
            //     Action::make('view')
            //         ->button()
            //         ->url(function() use ($vendor){
            //             $slug = OwnerAssociation::where('id',$vendor->owner_association_id)->first()?->slug;
            //             if($slug){
            //                 return VendorResource::getUrl('edit', [$slug,$vendor->id]);
            //             }
            //             return url('/app/facility-managers/' . $vendor->id.'/edit');
            //         }),
            // ])
            // ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Vendor "updated" event.
     */
    public function updated(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "deleted" event.
     */
    public function deleted(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "restored" event.
     */
    public function restored(Vendor $vendor): void
    {
        //
    }

    /**
     * Handle the Vendor "force deleted" event.
     */
    public function forceDeleted(Vendor $vendor): void
    {
        //
    }
}
