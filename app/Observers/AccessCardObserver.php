<?php

namespace App\Observers;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\AccessCard;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AccessCardObserver
{
    /**
     * Handle the AccessCard "created" event.
     */
    public function created(AccessCard $accessCard): void
    {
        $requiredPermissions = ['view_any_access::card::forms::document'];
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $accessCard->building_id, 'active'=> true])->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oa_ids)->where('role', 'Property Manager')->first();
        foreach($oa_ids as $oa_id){
            $oa = OwnerAssociation::find($oa_id);
            $flatexists = DB::table('property_manager_flats')
            ->where(['flat_id' => $accessCard->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
            ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)
                    ->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->post_id', $post?->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $slug = OwnerAssociation::where('id',$post?->owner_association_id)->first()?->slug;
                            if($slug){
                                $data['url']=AccessCardFormsDocumentResource::getUrl('edit', [$slug, $accessCard?->id]);
                            }else{
                                $data['url']=url('/app/access-card-forms-documents/' . $accessCard?->id.'/edit');
                            }
                            $data['title']='New Access Card Submission';
                            $data['body']='New form submission by ' . auth()->user()->first_name;
                            $data['building_id']=$accessCard->building_id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $accessCard->building_id,
                                'post_id' => $accessCard->id,
                                'user_id' => auth()->user()->id,
                                'owner_association_id' => $accessCard->owner_association_id,
                                'type' => 'Access Card',
                                'priority' => 'Medium',
                            ]);
                            NotificationTable($data);
                        }
                    }
                }
                    // Notification::make()
                    //     ->success()
                    //     ->title("New Access Card Submission")
                    //     ->icon('heroicon-o-document-text')
                    //     ->iconColor('warning')
                    //     ->body('New form submission by ' . auth()->user()->first_name)
                    //     ->actions([
                    //         Action::make('view')
                    //             ->button()
                    //             ->url(function() use ($oa,$accessCard){
                    //                 $slug = $oa?->slug;
                    //                 if($slug){
                    //                     return AccessCardFormsDocumentResource::getUrl('edit', [$slug,$accessCard?->id]);
                    //                 }
                    //                 return url('/app/access-card-forms-documents/' . $accessCard?->id.'/edit');
                    //             }),
                    //     ])
                    //     ->sendToDatabase($notifyTo);
            }
        }
    }
    /**
     * Handle the AccessCard "updated" event.
     */
    public function updated(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "deleted" event.
     */
    public function deleted(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "restored" event.
     */
    public function restored(AccessCard $accessCard): void
    {
        //
    }

    /**
     * Handle the AccessCard "force deleted" event.
     */
    public function forceDeleted(AccessCard $accessCard): void
    {
        //
    }
}
