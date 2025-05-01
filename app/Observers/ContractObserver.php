<?php

namespace App\Observers;

use App\Filament\Resources\ContractResource;
use App\Models\Accounting\Proposal;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $requiredPermissions = ['view_any_contract'];
        $user = auth()->user();
        $building = Building::where('id', $contract->building_id)->first();
        $oam_ids = DB::table('building_owner_association')
            ->where('building_id', $building?->id)->where('active', true)->pluck('owner_association_id');
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        foreach ($oam_ids as $oam_id) {
            $notifyTo = User::where('owner_association_id', $oam_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
            if($notifyTo->count() > 0){
                foreach($notifyTo as $user){
                    if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->contract_id', $contract->id)->exists()){
                        $data=[];
                        $data['notifiable_type']='App\Models\User\User';
                        $data['notifiable_id']=$user->id;
                        $data['url']=ContractResource::getUrl('edit', [$slug, $contract->id]);
                        $data['title']='New Contract for Building:'. $contract->building->name;
                        $data['body']='A new contract received from  '.auth()->user()->first_name;
                        $data['building_id']=$contract->building_id;
                        $data['custom_json_data']=json_encode([
                            'building_id' => $contract->building_id,
                            'contract_id' => $contract->id,
                            'user_id' => auth()->user()->id,
                            'owner_association_id' => $oam_id,
                            'type' => 'Contract',
                            'priority' => 'Medium',
                        ]);
                        NotificationTable($data);
                    }
                }
            }
                // Notification::make()
                // ->success()
                // ->title("New Contract")
                // ->icon('heroicon-o-document-text')
                // ->iconColor('warning')
                // ->body('New contract is created')
                // ->actions([
                //     Action::make('view')
                //         ->button()
                //         ->url(function() use ($oam_id,$contract){
                //             $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
                //             if($slug){
                //                 return ContractResource::getUrl('edit', [$slug,$contract?->id]);
                //             }
                //             return url('/app/contracts/' . $contract?->id.'/edit');
                //         }),
                // ])
                // ->sendToDatabase($notifyTo);
        }
    }
    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        //contract document updates vendor will notify
        if ($contract->document_url) {
            $vendor = Vendor::where('id', $contract->vendor_id)->first();
            DB::table('notifications')->insert([
                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id' => $vendor->owner_id,
                'data' => json_encode([
                    'actions' => [],
                    'body' => 'Contract document has been updated.',
                    'duration' => 'persistent',
                    'icon' => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title' => 'Contract Document Updates!',
                    'view' => 'notifications::notification',
                    'viewData' => [],
                    'format' => 'filament',
                    'url' => 'contract',
                ]),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }
}
