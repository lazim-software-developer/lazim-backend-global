<?php

namespace App\Observers;

use App\Models\User\User;
use App\Traits\UtilsTrait;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\AccountCredentials;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Support\Facades\Log;
use App\Models\ExpoPushNotification;
use Filament\Notifications\Notification;
use App\Filament\Resources\SnagsResource;
use Filament\Notifications\Actions\Action;
use App\Jobs\Complaint\ComplaintCreationJob;
use App\Filament\Resources\ComplaintsenquiryResource;
use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Filament\Resources\OacomplaintReportsResource;
use App\Filament\Resources\ComplaintscomplaintResource;
use App\Filament\Resources\ComplaintssuggessionResource;

class ComplaintObserver
{
    use UtilsTrait;
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
        $oam_ids = DB::table('building_owner_association')
            ->where(['building_id'=> $complaint->building_id,'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
        foreach($oam_ids as $oam_id){
            $oa = OwnerAssociation::find($oam_id);
            $notifyTo = User::where('owner_association_id', $oa->id)
                ->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
            $flatexists = $complaint?->flat_id ? DB::table('property_manager_flats')
                ->where(['flat_id' => $complaint?->flat_id, 'active' => true,'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists() : true;
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){

                if ($complaint->complaint_type == 'tenant_complaint') {
                    $requiredPermissions = ['view_any_complaintscomplaint'];
                            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                    Notification::make()
                        ->success()
                        ->title("Happiness center Complaint Received")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('Complaint has been created by' . auth()->user()->first_name)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(function() use ($complaint,$oa){
                                    $slug = $oa?->slug;
                                    if($slug){
                                        return ComplaintscomplaintResource::getUrl('edit', [$slug,$complaint?->id]);
                                    }
                                    return url('/app/facility-support-complaints/' . $complaint?->id.'/edit');
                                }),
                        ])
                        ->sendToDatabase($notifyTo);
                } elseif ($complaint->complaint_type == 'enquiries') {
                    $requiredPermissions = ['view_any_complaintsenquiry'];
                            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                    Notification::make()
                        ->success()
                        ->title("New Enquiry Received")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('A enquiry has been received raised by ' . auth()->user()->first_name)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(function() use ($complaint,$oa){
                                    $slug = $oa?->slug;
                                    if($slug){
                                        return ComplaintsenquiryResource::getUrl('edit', [$slug,$complaint?->id]);
                                    }
                                    return url('/app/complaintsenquiries/' . $complaint?->id.'/edit');
                                }),
                        ])
                        ->sendToDatabase($notifyTo);
                } elseif ($complaint->complaint_type == 'suggestions') {
                    $requiredPermissions = ['view_any_complaintssuggession'];
                            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                    Notification::make()
                        ->success()
                        ->title("New Suggestion Received")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('A suggestion made by ' . auth()->user()->first_name)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(function() use ($complaint,$oa){
                                    $slug = $oa?->slug;
                                    if($slug){
                                        return ComplaintssuggessionResource::getUrl('edit', [$slug,$complaint?->id]);
                                    }
                                    return url('/app/complaintssuggessions/' . $complaint?->id.'/edit');
                                }),
                        ])
                        ->sendToDatabase($notifyTo);
                } elseif($complaint->complaint_type == 'snag'){
                    $requiredPermissions = ['view_any_snags'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    Notification::make()
                    ->success()
                    ->title('New Snag')
                    ->body('New Snag Created')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->actions([
                        Action::make('View')
                        ->button()
                        ->url(function() use ($complaint,$oa){
                                $slug = $oa?->slug;
                                if($slug){
                                    return SnagsResource::getUrl('edit', [$slug,$complaint?->id]);
                                }
                                return url('/app/snags/' . $complaint?->id.'/edit');
                        }),
                    ])
                    ->sendToDatabase($notifyTo);
                }
                else {
                    $requiredPermissions = ['view_any_helpdeskcomplaint'];
                            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                    if(OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug){
                        Notification::make()
                            ->success()
                            ->title("Facility support Ticket Received")
                            ->icon('heroicon-o-document-text')
                            ->iconColor('warning')
                            ->body('A new ticket is raised by ' . auth()->user()->first_name)
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->url(function() use ($complaint,$oa){
                                        $slug = $oa?->slug;
                                        if($slug){
                                            return HelpdeskcomplaintResource::getUrl('edit', [$slug,$complaint?->id]);
                                        }
                                        return url('/app/facility-support-complaints/' . $complaint?->id.'/edit');
                                    }),
                            ])
                            ->sendToDatabase($notifyTo);
                    }
                }
            }
        }

        //Notifying to vendor when type is tenant_complaint or help_desk
        if ($complaint->vendor_id) {
            $vendor = Vendor::where('id', $complaint->vendor_id)->first();
            if ($complaint->complaint_type == 'tenant_complaint') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $vendor->owner_id,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Complaint has been created by ' . auth()->user()->first_name,
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'Complaint Received',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => 'task',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            if ($complaint->complaint_type == 'help_desk') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $vendor->owner_id,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Complaint has been created by ' . auth()->user()->first_name,
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'Complaint Received',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'url' => 'task',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
        // notify tech when created
        if ($complaint->technician_id) {
            $credentials     = AccountCredentials::where('oa_id', $complaint->owner_association_id)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];
            ComplaintCreationJob::dispatch($complaint->id, $complaint->technician_id, $mailCredentials);

            $expoPushToken = ExpoPushNotification::where('user_id', $complaint->technician_id)->first()?->token;
            if ($expoPushToken) {
                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'title' => $complaint->complaint_type == 'preventive_maintenance' ? 'Preventive Maintenance Schedule Assigned' : 'Task Assigned',
                    'body'  => $complaint->complaint_type == 'preventive_maintenance' ? 'Preventive Maintenance Schedule has been assigned' : 'Task has been assigned',
                    'data'  => ['notificationType' => 'PendingRequests'],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $complaint->technician_id,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => $complaint->complaint_type == 'preventive_maintenance' ? 'Preventive Maintenance Schedule has been assigned' : 'Task has been assigned',
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => $complaint->complaint_type == 'preventive_maintenance' ? 'Preventive Maintenance Schedule Assigned' : 'Task Assigned',
                        'view'      => 'notifications::notification',
                        'viewData'  => [],
                        'format'    => 'filament',
                        'url'       => 'PendingRequests',
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                Log::info("No technicians to add", []);
            }
        }
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        $user = auth()->user();
        $oldValues = $complaint->getOriginal();
        $newValues = $complaint->getAttributes();
        $oam_ids = DB::table('building_owner_association')
            ->where(['building_id'=> $complaint->building_id,'active'=> true])
            ->pluck('owner_association_id');
        $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
        foreach($oam_ids as $oam_id){
            $oa = OwnerAssociation::find($oam_id);
            $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)
                ->whereNot('id', auth()->user()?->id)->get();
            //DB notification for ADMIN status update from resident/technician
            $flatexists = $complaint?->flat_id ? DB::table('property_manager_flats')
                ->where(['flat_id' => $complaint?->flat_id, 'active' => true,'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                ->exists() : true;
            if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                if ($complaint->status == 'closed') {
                    if ($complaint->complaint_type == 'help_desk') {
                        $requiredPermissions = ['view_any_helpdeskcomplaint'];
                            $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                        Notification::make()
                            ->success()
                            ->title("Facility Support Issue Resolution")
                            ->icon('heroicon-o-document-text')
                            ->iconColor('warning')
                            ->body('Issue has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name)
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->url(function() use ($complaint,$oa){
                                        $slug = $oa?->slug;
                                        if($slug){
                                            return HelpdeskcomplaintResource::getUrl('edit', [$slug,$complaint?->id]);
                                        }
                                        return url('/app/facility-support-complaints' . $complaint?->id.'/edit');
                                    }),
                            ])
                            ->sendToDatabase($notifyTo);
                    } elseif ($complaint->complaint_type == 'oa_complaint_report'){
                        $requiredPermissions = ['view_any_oacomplaint::reports'];
                        $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                            return $notifyTo->can($requiredPermissions);
                        });
                        Notification::make()
                            ->success()
                            ->title("Complaints Resolved")
                            ->icon('heroicon-o-document-text')
                            ->iconColor('warning')
                            ->body('Complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name)
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->url(function() use ($complaint,$oa){
                                        $slug = $oa?->slug;
                                        if($slug){
                                            return OacomplaintReportsResource::getUrl('edit', [$slug,$complaint?->id]);
                                        }
                                        return url('/app/oacomplaint-reports/' . $complaint?->id.'/edit');
                                    }),
                            ])
                            ->sendToDatabase($notifyTo);
                    }
                    else {
                        // $requiredPermissions = ['view_any_helpdeskcomplaint'];
                            $notifyTo->whereIn('role_id', Role::whereIn('name', ['OA','Property Manager'])->pluck('id'));
                        Notification::make()
                            ->success()
                            ->title(($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint')." Resolved")
                            ->icon('heroicon-o-document-text')
                            ->iconColor('warning')
                            ->body(($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name)
                            ->sendToDatabase($notifyTo);
                    }
                }
            }
        }

        //assign technician notification to assigned technician (assigned by 'OA', 'Vendor')
        $allowedRole = ['OA', 'Vendor','Facility Manager','Property Manager'];
        if($complaint->technician_id){
            if (in_array($user->role->name, $allowedRole)) {
                if ($complaint->technician_id != $newValues['technician_id']) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'New '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assigned',
                                'body' => 'A new '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' assigned to you.',
                                'data' => ['notificationType' => 'PendingRequests'],
                            ];
                            $this->expoNotification($message);
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $complaint->technician_id,
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'A new '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' assigned to you.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assigned',
                                    'view' => 'notifications::notification',
                                    'viewData' => [],
                                    'format' => 'filament',
                                    'url' => 'PendingRequests',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }

                //if technician updated then older technician will notify
                if ($newValues['technician_id'] != $oldValues['technician_id']) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $oldValues['technician_id'])->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assignment Status',
                                'body' => 'You have been relived from the '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' by the vendor.',
                                'data' => ['notificationType' => 'PendingRequests'],
                            ];
                            $this->expoNotification($message);
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $oldValues['technician_id'],
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'You have been relived from the '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' by the vendor.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assignment Status',
                                    'view' => 'notifications::notification',
                                    'viewData' => [],
                                    'format' => 'filament',
                                    'url' => 'PendingRequests',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }

                //if technician updated then new technician will notify
                if ($newValues['technician_id'] != $oldValues['technician_id']) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $newValues['technician_id'])->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'New '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assigned',
                                'body' => 'A new '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' assigned to you.',
                                'data' => ['notificationType' => 'PendingRequests'],
                            ];
                            $this->expoNotification($message);
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $newValues['technician_id'],
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'A new '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' assigned to you.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assigned',
                                    'view' => 'notifications::notification',
                                    'viewData' => [],
                                    'format' => 'filament',
                                    'url' => 'PendingRequests',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }

                    //if OA is updating the due_date then vendor will notify
                    if($complaint->vendor_id){
                        if ($user->role->name == 'OA') {
                            $technician = User::where('id', $newValues['technician_id'])->first();
                            $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $vendor->owner_id,
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'A new technician ' . $technician->first_name . ' is assigned to you.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New Technician Assigned',
                                    'view' => 'notifications::notification',
                                    'viewData' => [
                                        'complaintId'      => $complaint?->id,
                                        'open_time' => $complaint?->open_time,
                                        'close_time' => $complaint?->close_time,
                                        'due_date' => $complaint?->due_date,
                                    ],
                                    'format' => 'filament',
                                    'url' => $complaint->complaint_type == 'preventive_maintenance' ? 'PreventiveMaintenance' : 'task',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }
        }

        //complaints status update push notification to technician mobile app (if closed by 'Owner', 'OA', 'Tenant')
        $allowedRoles = ['Owner', 'OA', 'Tenant','Facility Manager','Property Manager'];
        if($complaint->technician_id){
            if (in_array($user->role->name, $allowedRoles)) {
                if ($complaint->status == 'closed') {
                    if ($complaint->complaint_type == 'help_desk') {
                        $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                                    'body' => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                    'data' => ['notificationType' => 'ResolvedRequests'],
                                ];
                                $this->expoNotification($message);
                                DB::table('notifications')->insert([
                                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                    'type' => 'Filament\Notifications\DatabaseNotification',
                                    'notifiable_type' => 'App\Models\User\User',
                                    'notifiable_id' => $complaint->technician_id,
                                    'data' => json_encode([
                                        'actions' => [],
                                        'body' => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                        'duration' => 'persistent',
                                        'icon' => 'heroicon-o-document-text',
                                        'iconColor' => 'warning',
                                        'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                                        'view' => 'notifications::notification',
                                        'viewData' => [],
                                        'format' => 'filament',
                                        'url' => 'ResolvedRequests',
                                    ]),
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                    if ($complaint->complaint_type == 'preventive_maintenance') {
                        $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                                    'body' => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been completed by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                    'data' => ['notificationType' => 'ResolvedRequests'],
                                ];
                                $this->expoNotification($message);
                                DB::table('notifications')->insert([
                                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                    'type' => 'Filament\Notifications\DatabaseNotification',
                                    'notifiable_type' => 'App\Models\User\User',
                                    'notifiable_id' => $complaint->technician_id,
                                    'data' => json_encode([
                                        'actions' => [],
                                        'body' => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been completed by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                        'duration' => 'persistent',
                                        'icon' => 'heroicon-o-document-text',
                                        'iconColor' => 'warning',
                                        'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' status',
                                        'view' => 'notifications::notification',
                                        'viewData' => [],
                                        'format' => 'filament',
                                        'url' => 'ResolvedRequests',
                                    ]),
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                        $residentIds = FlatTenant::where([
                            'building_id' => $complaint->building_id,
                            'active'      => true,
                        ])->distinct()->pluck('tenant_id');
                        if ($residentIds->count() > 0) {
                            // Create individual notifications for each resident
                            foreach ($residentIds as $residentId) {
                                $residentTokens = ExpoPushNotification::where('user_id', $residentId)->first()?->token;
                                $message        = [
                                    'to'    => $residentTokens,
                                    'sound' => 'default',
                                    'title' => 'Preventive Maintenance status',
                                    'body'  => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been completed by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                    'data'  => [
                                        'notificationType' => 'PreventiveMaintenance',
                                        'complaintId'      => $complaint?->id,
                                        'open_time'        => $complaint?->open_time,
                                        'close_time'       => $complaint?->close_time,
                                        'due_date'         => $complaint?->due_date,
                                    ],
                                ];
                                $this->expoNotification($message);
                                DB::table('notifications')->insert([
                                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                    'type'            => 'Filament\Notifications\DatabaseNotification',
                                    'notifiable_type' => 'App\Models\User\User',
                                    'notifiable_id'   => $residentId,
                                    'data'            => json_encode([
                                        'actions'   => [],
                                        'body'      => 'A '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been completed by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                        'duration'  => 'persistent',
                                        'icon'      => 'heroicon-o-document-text',
                                        'iconColor' => 'warning',
                                        'title'     => 'Preventive Maintenance status',
                                        'view'      => 'notifications::notification',
                                        'viewData'  => [
                                            'complaintId' => $complaint?->id,
                                            'open_time'   => $complaint?->open_time,
                                            'close_time'  => $complaint?->close_time,
                                            'due_date'    => $complaint?->due_date,
                                        ],
                                        'format'    => 'filament',
                                        'url'       => 'PreventiveMaintenance',
                                    ]),
                                    'created_at'      => now()->format('Y-m-d H:i:s'),
                                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                                ]);
                            }
                        }

                    }
                }

            }
        }

        //if due_date updated then assign technician will get the notification
        if($complaint->technician_id){
            if ($newValues['due_date'] != $oldValues['due_date']) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' Date has been updated',
                            'body' => 'Date for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                            'data' => ['notificationType' => 'PendingRequests'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->technician_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Due date for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' Date has been updated',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => 'PendingRequests',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                //if OA is updating the due_date then vendor will notify
                if($complaint->vendor_id){
                    if (in_array($user->role->name, ['OA','Property Manager'])) {
                        $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $vendor->owner_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Due date for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Date Changes',
                                'view' => 'notifications::notification',
                                'viewData' => [
                                    'complaintId'      => $complaint?->id,
                                    'open_time' => $complaint?->open_time,
                                    'close_time' => $complaint?->close_time,
                                    'due_date' => $complaint?->due_date,
                                ],
                                'format' => 'filament',
                                'url' => $complaint->complaint_type == 'preventive_maintenance' ? 'PreventiveMaintenance' : 'task',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        //if priority updated then assign technician will get the notification
        if($complaint->technician_id){
            if ($newValues['priority'] != $oldValues['priority']) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' Priority has been updated',
                            'body' => 'Priority for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                            'data' => ['notificationType' => 'PendingRequests'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->technician_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Priority for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Schedule' : 'complaint').' Priority has been updated',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => 'PendingRequests',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                //if OA is updating the priority then vendor will notify
                if($complaint->vendor_id){
                    if ($user->role->name == 'OA') {
                        $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $vendor->owner_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Priority for '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => ($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Priority Changes',
                                'view' => 'notifications::notification',
                                'viewData' => [
                                    'complaintId'      => $complaint?->id,
                                    'open_time' => $complaint?->open_time,
                                    'close_time' => $complaint?->close_time,
                                    'due_date' => $complaint?->due_date,
                                ],
                                'format' => 'filament',
                                'url' => $complaint->complaint_type == 'preventive_maintenance' ? 'PreventiveMaintenance' : 'task',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        //if complaint id resolved by OA admin, technician, owner,Tenant then vendor will notify
        $allowedRoles = ['Owner', 'OA', 'Technician', 'Tenant'];
        if ($complaint->vendor_id) {
            if (in_array($user->role->name, $allowedRoles)) {
                if ($complaint->status == 'closed') {
                    if ($complaint->complaint_type == 'help_desk' || $complaint->complaint_type == 'tenant_complaint') {
                        $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $vendor->owner_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'A complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint status',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => 'task',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        //when new technician is assigned to vendor, will notify to vendor
        if($complaint->vendor_id){
            if ($user->role->name == 'OA') {
                if ($complaint->technician_id && $complaint->technician_id != $newValues['technician_id']) {
                    $technician = User::where('id', $newValues['technician_id'])->first();
                    $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $vendor->owner_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'A new technician ' . $technician->first_name . ' is assigned to you.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'New '.($complaint->complaint_type === 'preventive_maintenance' ? 'Preventive Maintenance' : 'complaint').' Assigned',
                            'view' => 'notifications::notification',
                            'viewData' => [
                                'complaintId'      => $complaint?->id,
                                'open_time' => $complaint?->open_time,
                                'close_time' => $complaint?->close_time,
                                'due_date' => $complaint?->due_date,
                            ],
                            'format' => 'filament',
                            'url' => $complaint->complaint_type == 'preventive_maintenance' ? 'PreventiveMaintenance' : 'task',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

    }

    /**
     * Handle the Complaint "deleted" event.
     */
    public function deleted(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "restored" event.
     */
    public function restored(Complaint $complaint): void
    {
        //
    }

    /**
     * Handle the Complaint "force deleted" event.
     */
    public function forceDeleted(Complaint $complaint): void
    {
        //
    }
}
