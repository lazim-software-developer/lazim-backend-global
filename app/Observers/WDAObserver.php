<?php

namespace App\Observers;

use App\Filament\Resources\WDAResource;
use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class WDAObserver
{
    /**
     * Handle the WDA "created" event.
     */
    public function created(WDA $WDA): void
    {
            $requiredPermissions = ['view_any_w::d::a'];
            $oam_ids = DB::table('building_owner_association')->where('building_id', $WDA?->building_id)
                ->where('active', true)->pluck('owner_association_id');
            $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])
                ->pluck('id');
            foreach($oam_ids as $oam_id){
                $notifyTo = User::where('owner_association_id', $oam_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->wda_id', $WDA->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
                            if($slug){
                                $data['url']=WDAResource::getUrl('edit', [$slug,$WDA->id]);
                            }else{
                                $data['url']=url('/app/w-d-a-s/' . $WDA?->id.'/edit');
                            }
                            $data['title']="New WDA Form for Building: " . Building::where('id', $WDA?->building_id)->value('name');
                            $data['body']='New WDA form submitted by  ' . auth()->user()->first_name;
                            $data['building_id']=$WDA->building_id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $WDA->building_id,
                                'wda_id' => $WDA->id,
                                'user_id' => auth()->user()->id ?? null,
                                'owner_association_id' => $oam_id,
                                'type' => 'WDA',
                                'priority' => 'Medium',
                            ]);
                            NotificationTable($data);
                        }
                    }
                }
                // Notification::make()
                //     ->success()
                //     ->title("New WDA Form for Building: " . Building::where('id', $WDA?->building_id)->value('name'))
                //     ->icon('heroicon-o-document-text')
                //     ->iconColor('warning')
                //     ->body('New WDA form submitted by  ' . auth()->user()->first_name)
                //     ->actions([
                //         Action::make('view')
                //             ->button()
                //             ->url(function() use ($oam_id,$WDA){
                //                 $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
                //                 if($slug){
                //                     return WDAResource::getUrl('edit', [$slug,$WDA->id]);
                //                 }
                //                 return url('/app/w-d-a-s/' . $WDA?->id.'/edit');
                //             }),
                //     ])
                //     ->sendToDatabase($notifyTo);
            }
    }

    /**
     * Handle the WDA "updated" event.
     */
    public function updated(WDA $wDA): void
    {
        $user = auth()->user();
        if ($user->role->name == 'OA') {
            if ($wDA->status == 'approved') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $wDA->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'title' => 'WDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your WDA has been approved.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => 'wda',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            if ($wDA->status == 'rejected') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $wDA->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'title' => 'WDA status update.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'body' => 'Your WDA has been rejected.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => 'wda',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }

        }
    }

    /**
     * Handle the WDA "deleted" event.
     */
    public function deleted(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "restored" event.
     */
    public function restored(WDA $wDA): void
    {
        //
    }

    /**
     * Handle the WDA "force deleted" event.
     */
    public function forceDeleted(WDA $wDA): void
    {
        //
    }
}
