<?php

namespace App\Observers;

use App\Filament\Resources\ResidentialFormResource;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ResidentialFormObserver
{
    /**
     * Handle the ResidentialForm "created" event.
     */
    public function created(ResidentialForm $residentialForm): void
    {
        $requiredPermissions = ['view_any_residential::form'];
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])
            ->pluck('id');
        $oam_ids = DB::table('building_owner_association')
            ->where(['building_id' => $residentialForm->building_id, 'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
        foreach($oam_ids as $oam_id){
            $oa = OwnerAssociation::find($oam_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $residentialForm->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->residential_form_id', $residentialForm->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $data['url']=ResidentialFormResource::getUrl('edit', [$oa?->slug,$residentialForm->id]);
                            $data['title']="New Residential Form Submission for Building".$residentialForm->flat?->building?->name;
                            $data['body']='New form submission by'.auth()->user()->first_name;
                            $data['building_id']=$residentialForm->flat?->building?->id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $residentialForm->flat?->building?->id,
                                'residential_form_id' => $residentialForm->id,
                                'user_id' => auth()->user()->id ?? null,
                                'owner_association_id' => $oa->id,
                                'type' => 'ResidentialForm',
                                'priority' => 'Medium',
                            ]);
                            NotificationTable($data);
                        }
                    }
                }

                // Notification::make()
                // ->success()
                // ->title("New Residential Form Submission")
                // ->icon('heroicon-o-document-text')
                // ->iconColor('warning')
                // ->body('New form submission by'.auth()->user()->first_name)
                // ->actions([
                //     Action::make('view')
                //         ->button()
                //         ->url(function() use ($residentialForm,$oa){
                //             $slug = $oa?->slug;
                //             if($slug){
                //                 return ResidentialFormResource::getUrl('edit', [$slug,$residentialForm?->id]);
                //             }
                //             return url('/app/residential-forms/' . $residentialForm?->id.'/edit');
                //         }),
                // ])
                // ->sendToDatabase($notifyTo);
            }
        }
    }

    /**
     * Handle the ResidentialForm "updated" event.
     */
    public function updated(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "deleted" event.
     */
    public function deleted(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "restored" event.
     */
    public function restored(ResidentialForm $residentialForm): void
    {
        //
    }

    /**
     * Handle the ResidentialForm "force deleted" event.
     */
    public function forceDeleted(ResidentialForm $residentialForm): void
    {
        //
    }
}
