<?php

namespace App\Observers;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class FitOutFormObserver
{
    /**
     * Handle the FitOutForm "created" event.
     */
    public function created(FitOutForm $fitOutForm): void
    {
        $requiredPermissions = ['view_any_fit::out::forms::document'];
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $fitOutForm->building_id,'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oa_ids)->where('role', 'Property Manager')->first();
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        foreach($oa_ids as $oa_id){
            $oa = OwnerAssociation::find($oa_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $fitOutForm->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                 
                // Get the owner association for URL generation
                $ownerAssociation = OwnerAssociation::find($oa_id);
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->fit_out_form_id', $fitOutForm->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $data['url']=FitOutFormsDocumentResource::getUrl('edit', [$ownerAssociation->slug, $fitOutForm->id]);
                            $data['title']="New Fitout Form Submission for Building:".$fitOutForm->building->name;
                            $data['body']='New form submission by '.auth()->user()->first_name;
                            $data['building_id']=$fitOutForm->building_id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $fitOutForm->building_id,
                                'fit_out_form_id' => $fitOutForm->id,
                                'user_id' => auth()->user()->id,
                                'owner_association_id' => $oa_id,
                                'type' => 'FitOutForm',
                                'priority' => 'Medium',
                            ]);
                            NotificationTable($data);
                        }
                    }
                }
                // Notification::make()
                // ->success()
                // ->title("New Fitout Form Submission")
                // ->icon('heroicon-o-document-text')
                // ->iconColor('warning')
                // ->body('New form submission by '.auth()->user()->first_name)
                // ->actions([
                //     Action::make('view')
                //         ->button()
                //         ->url(function() use ($fitOutForm, $ownerAssociation){
                //             if($ownerAssociation && $ownerAssociation->slug){
                //                 return FitOutFormsDocumentResource::getUrl('edit', [$ownerAssociation->slug, $fitOutForm->id]);
                //             }
                //             return url('/app/fit-out-forms-documents/' . $fitOutForm->id.'/edit');
                //         }),
                // ])
                // ->sendToDatabase($notifyTo);
            }
        }
    }

    /**
     * Handle the FitOutForm "updated" event.
     */
    public function updated(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "deleted" event.
     */
    public function deleted(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "restored" event.
     */
    public function restored(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "force deleted" event.
     */
    public function forceDeleted(FitOutForm $fitOutForm): void
    {
        //
    }
}
