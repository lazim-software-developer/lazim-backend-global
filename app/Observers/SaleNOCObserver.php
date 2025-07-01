<?php

namespace App\Observers;

use App\Filament\Resources\NocFormResource;
use App\Models\Building\Building;
use App\Models\Forms\SaleNOC;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class SaleNOCObserver
{
    /**
     * Handle the SaleNOC "created" event.
     */
    public function created(SaleNOC $saleNOC): void
    {
        $requiredPermissions = ['view_any_noc::form'];
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        $oam_ids = DB::table('building_owner_association')
            ->where(['building_id' => $saleNOC->building_id, 'active' => true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
        foreach($oam_ids as $oam_id){
            $oa = OwnerAssociation::find($oam_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $saleNOC->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->sale_noc_id', $saleNOC->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $slug = $oa?->slug;
                            if($slug){
                                $data['url']=NocFormResource::getUrl('edit', [$slug,$saleNOC?->id]);
                            }else{
                                $data['url']=url('/app/sale-nocs/' . $saleNOC?->id.'/edit');
                            }
                            $data['title']="New Sale Noc Submission for Building :". $saleNOC->building->name;
                            $data['body']='New form submission by '.auth()->user()->first_name;
                            $data['building_id']=$saleNOC->building_id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $saleNOC->building_id,
                                'sale_noc_id' => $saleNOC->id,
                                'user_id' => auth()->user()->id ?? null,
                                'owner_association_id' => $oa->id,
                                'type' => 'SaleNoc',
                                'priority' => 'High',
                            ]);
                            NotificationTable($data);
                        }
            }
        }
            }
        }
    }

    /**
     * Handle the SaleNOC "updated" event.
     */
    public function updated(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "deleted" event.
     */
    public function deleted(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "restored" event.
     */
    public function restored(SaleNOC $saleNOC): void
    {
        //
    }

    /**
     * Handle the SaleNOC "force deleted" event.
     */
    public function forceDeleted(SaleNOC $saleNOC): void
    {
        //
    }
}
