<?php

namespace App\Observers;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Filament\Resources\MoveInFormsDocumentResource;
use App\Filament\Resources\MoveOutFormsDocumentResource;
use App\Jobs\MoveoutNotificationJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\Building\FlatTenant;
use App\Models\Forms\MoveInOut;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class MoveInOutObserver
{
    /**
     * Handle the MoveInOut "created" event.
     */
    public function created(MoveInOut $moveInOut): void
    {
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $moveInOut->building_id, 'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oa_ids)->where('role', 'Property Manager')->first();
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
        foreach($oa_ids as $oa_id){
            $oa = OwnerAssociation::find($oa_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $moveInOut->flat_id, 'active' => true,'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists();
            $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)
                ->whereNot('id', auth()->user()?->id)->get();
            if($moveInOut->type == 'move-in'){
                if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                    $requiredPermissions = ['view_any_move::in::forms::document'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    if($notifyTo->count() > 0){
                        foreach($notifyTo as $user){
                            if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->move_in_id', $moveInOut->id)->exists()){
                                $data=[];
                                $data['notifiable_type']='App\Models\User\User';
                                $data['notifiable_id']=$user->id;
                                $slug = $oa?->slug;
                                if($slug){
                                    $data['url']=MoveInFormsDocumentResource::getUrl('edit', [$slug,$moveInOut?->id]);
                                }else{
                                    $data['url']=url('/app/move-in-forms-documents/' . $moveInOut?->id.'/edit');
                                }
                                $data['title']="New Move in Submission for Building:".$moveInOut->building->name;
                                $data['body']='New form submission by '.auth()->user()->first_name;
                                $data['building_id']=$moveInOut->building_id;
                                $data['custom_json_data']=json_encode([
                                    'building_id' => $moveInOut->building_id,
                                    'move_in_id' => $moveInOut->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'owner_association_id' => $oa->id,
                                    'type' => 'Move in',
                                    'priority' => 'Medium',
                                ]);
                                NotificationTable($data);
                            }
                        }
                    }
                    // Notification::make()
                    // ->success()
                    // ->title("New Move in Submission")
                    // ->icon('heroicon-o-document-text')
                    // ->iconColor('warning')
                    // ->body('New form submission by '.auth()->user()->first_name)
                    // ->actions([
                    //     Action::make('view')
                    //         ->button()
                    //         ->url(function() use ($moveInOut,$oa){
                    //             $slug = $oa?->slug;
                    //             if($slug){
                    //                 return MoveInFormsDocumentResource::getUrl('edit', [$slug,$moveInOut?->id]);
                    //             }
                    //             return url('/app/move-in-forms-documents/' . $moveInOut?->id.'/edit');
                    //         }),
                    // ])
                    // ->sendToDatabase($notifyTo);
                }
            }
            else{
                if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                    $requiredPermissions = ['view_any_move::out::forms::document'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    if($notifyTo->count() > 0){
                        foreach($notifyTo as $user){
                            if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->move_out_id', $moveInOut->id)->exists()){
                                $data=[];
                                $data['notifiable_type']='App\Models\User\User';
                                $data['notifiable_id']=$user->id;
                                $slug = $oa?->slug;
                                if($slug){
                                    $data['url']=MoveOutFormsDocumentResource::getUrl('edit', [$slug,$moveInOut?->id]);
                                }else{
                                    $data['url']=url('/app/move-out-forms-documents/' . $moveInOut?->id.'/edit');
                                }
                                $data['title']="New Move out Submission for Building:".$moveInOut->building->name;
                                $data['body']='New form submission by '.auth()->user()->first_name;
                                $data['building_id']=$moveInOut->building_id;
                                $data['custom_json_data']=json_encode([
                                    'building_id' => $moveInOut->building_id,
                                    'move_out_id' => $moveInOut->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'owner_association_id' => $oa->id,
                                    'type' => 'Move out',
                                    'priority' => 'Medium',
                                ]);
                                NotificationTable($data);
                            }
                        }
                    }
                    // Notification::make()
                    // ->success()
                    // ->title("New Move out Submission")
                    // ->icon('heroicon-o-document-text')
                    // ->iconColor('warning')
                    // ->body('New form submission by '.auth()->user()->first_name)
                    // ->actions([
                    //     Action::make('view')
                    //         ->button()
                    //         ->url(function() use ($moveInOut,$oa){
                    //             $slug = $oa?->slug;
                    //             if($slug){
                    //                 return MoveOutFormsDocumentResource::getUrl('edit', [$slug,$moveInOut?->id]);
                    //             }
                    //             return url('/app/move-out-forms-documents/' . $moveInOut?->id.'/edit');
                    //         }),
                    // ])
                    // ->sendToDatabase($notifyTo);
                }
            }
        }
        if ($moveInOut->moving_date < now()->subDay()->toDateString() && $moveInOut->type != 'move-in') {
            $moveout = $moveInOut;
            $user    = User::whereHas('role', function ($query) {
                $query->where('name', 'OA');
            })->where('owner_association_id', $moveout->owner_association_id)->first();
            $flatTenat = FlatTenant::where('tenant_id', $moveout->user_id)->where('flat_id', $moveout->flat_id)->first();
            Notification::make()
                ->success()
                ->title("Moveout")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('There is a resident moving out on ' . $moveout->moving_date)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function () use ($moveout, $flatTenat) {
                            $slug = OwnerAssociation::where('id', $moveout->owner_association_id)->first()?->slug;
                            if ($slug) {
                                return FlatTenantResource::getUrl('edit', [$slug, $flatTenat?->id]);
                            }
                            return url('/app/building/flat-tenants/' . $flatTenat?->id . '/edit');
                        }),
                ])
                ->sendToDatabase($user);
            $credentials     = AccountCredentials::where('oa_id', $moveout->owner_association_id)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];
            MoveoutNotificationJob::dispatch($user, $moveout, $mailCredentials);
        }
    }

    /**
     * Handle the MoveInOut "updated" event.
     */
    public function updated(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "deleted" event.
     */
    public function deleted(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "restored" event.
     */
    public function restored(MoveInOut $moveInOut): void
    {
        //
    }

    /**
     * Handle the MoveInOut "force deleted" event.
     */
    public function forceDeleted(MoveInOut $moveInOut): void
    {
        //
    }
}
