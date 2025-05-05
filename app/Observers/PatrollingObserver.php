<?php

namespace App\Observers;

use App\Filament\Resources\PatrollingResource;
use App\Models\Gatekeeper\Patrolling;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PatrollingObserver
{
    /**
     * Handle the Patrolling "created" event.
     */
    public function created(Patrolling $patrolling): void
    {
        $requiredPermissions = ['view_any_patrolling'];
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $patrolling->building_id, 'active'=> true])
            ->pluck('owner_association_id');
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])
            ->pluck('id');
        foreach($oa_ids as $oa_id){
            $notifyTo = User::where('owner_association_id', $oa_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });//MAKE AUTH USER ID IN USER WHERENOT-----------
            if($notifyTo->count() > 0){
                foreach($notifyTo as $user){
                    if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->patrolling_id', $patrolling->id)->exists()){
                        $data=[];
                        $data['notifiable_type']='App\Models\User\User';
                        $data['notifiable_id']=$user->id;
                        $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
                        $data['url']=PatrollingResource::getUrl('index', [$slug]);
                        $data['title']="New Patrolling";
                        $data['body']='New patrolling details is received';
                        $data['building_id']=$patrolling->building_id;
                        $data['custom_json_data']=json_encode([
                            'building_id' => $patrolling->building_id,
                            'patrolling_id' => $patrolling->id,
                            'user_id' => auth()->user()->id ?? null,
                            'owner_association_id' => $oa_id,
                            'type' => 'Patrolling',
                            'priority' => 'Medium',
                        ]);
                        NotificationTable($data);
                    }
                }
            }
            // Notification::make()
            // ->success()
            // ->title('New Patrolling')
            // ->body('New Patrolling Details is Received')
            // ->icon('heroicon-o-document-text')
            // ->iconColor('warning')
            // ->actions([
            //     Action::make('View')
            //     ->button()
            //     ->url(function() use ($patrolling,$oa_id){
            //         $slug = OwnerAssociation::where('id',$oa_id)->first()?->slug;
            //         if($slug){
            //             return PatrollingResource::getUrl('index', [$slug]);
            //         }
            //         return url('/app/patrollings');
            //     }),

            // ])
            // ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the Patrolling "updated" event.
     */
    public function updated(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "deleted" event.
     */
    public function deleted(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "restored" event.
     */
    public function restored(Patrolling $patrolling): void
    {
        //
    }

    /**
     * Handle the Patrolling "force deleted" event.
     */
    public function forceDeleted(Patrolling $patrolling): void
    {
        //
    }
}
