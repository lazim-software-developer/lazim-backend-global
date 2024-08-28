<?php

namespace App\Observers;

use App\Filament\Resources\ComplaintscomplaintResource;
use App\Filament\Resources\ComplaintsenquiryResource;
use App\Filament\Resources\ComplaintssuggessionResource;
use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Filament\Resources\SnagsResource;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Traits\UtilsTrait;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ComplaintObserver
{
    use UtilsTrait;
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        $roles = Role::where('owner_association_id',$complaint->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $complaint->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
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
                        ->url(fn () => ComplaintscomplaintResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id])),
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
                        ->url(fn () => ComplaintsenquiryResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id])),
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
                        ->url(fn () => ComplaintssuggessionResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id])),
                ])
                ->sendToDatabase($notifyTo);
        } elseif($complaint->complaint_type == 'snag'){
            $requiredPermissions = ['view_any_snags'];
            $roles = Role::where('owner_association_id',$complaint->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
             $notifyTo = User::where('owner_association_id', $complaint->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
            ->filter(function ($notifyTo) use ($requiredPermissions) {
                return $notifyTo->can($requiredPermissions);
            });
            Notification::make()
            ->success()
            ->title('New Snag')
            ->body('New Snag Received')
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->actions([
                Action::make('View')
                ->button()
                ->url(fn () => SnagsResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id]))
            ])
        ->sendToDatabase($notifyTo);
        }
        else {
            $requiredPermissions = ['view_any_helpdeskcomplaint'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
            Notification::make()
                ->success()
                ->title("Facility support Ticket Received")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A new ticket is raised by ' . auth()->user()->first_name)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(fn () => HelpdeskcomplaintResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id])),
                ])
                ->sendToDatabase($notifyTo);
        }

        //Notifying to vendor
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
                        'url' => '',
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
                        'url' => '',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
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
        $building = Building::where('id', $complaint->building_id)->first();
        $roles = Role::where('owner_association_id',$building->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
        //DB notification for ADMIN status update from resident/technician
        if ($complaint->status == 'closed') {
            if ($complaint->complaint_type == 'help_desk') {
                $requiredPermissions = ['view_any_helpdeskcomplaint'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                Notification::make()
                    ->success()
                    ->title("Facility Support Complaint Resolution ")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('Complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name)
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn () => HelpdeskcomplaintResource::getUrl('edit', [OwnerAssociation::where('id',$complaint->owner_association_id)->first()?->slug,$complaint->id])),
                    ])
                    ->sendToDatabase($notifyTo);
            } else {
                // $requiredPermissions = ['view_any_helpdeskcomplaint'];
                    $notifyTo->where('role_id', Role::where('name', 'OA')->first()->id);
                Notification::make()
                    ->success()
                    ->title("Complaints Resolved")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('Complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name)
                    ->sendToDatabase($notifyTo);
            }
        }

        //assign technician notification to assigned technician (assigned by 'OA', 'Vendor')
        $allowedRole = ['OA', 'Vendor'];
        if($complaint->technician_id){
            if (in_array($user->role->name, $allowedRole)) {
                if ($complaint->technician_id != $newValues['technician_id']) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'New Complaint Assigned',
                                'body' => 'A new complaint assigned to you.',
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
                                    'body' => 'A new complaint assigned to you.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New Complaint Assigned',
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
                                'title' => 'Complaint Assignment Status',
                                'body' => 'You have been relived from the complaint by the vendor.',
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
                                    'body' => 'You have been relived from the complaint by the vendor.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'Complaint Assignment Status',
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
                                'title' => 'New Complaint Assigned',
                                'body' => 'A new complaint assigned to you.',
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
                                    'body' => 'A new complaint assigned to you.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New Complaint Assigned',
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
                                    'viewData' => [],
                                    'format' => 'filament',
                                    'url' => '',
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
        $allowedRoles = ['Owner', 'OA', 'Tenant'];
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
                                    'title' => 'Complaint status',
                                    'body' => 'A complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name,
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
                                        'body' => 'A complaint has been resolved by a ' . $user->role->name . ' ' . auth()->user()->first_name,
                                        'duration' => 'persistent',
                                        'icon' => 'heroicon-o-document-text',
                                        'iconColor' => 'warning',
                                        'title' => 'Complaint status',
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
                            'title' => 'Complaint Date Changes',
                            'body' => 'Due date for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
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
                                'body' => 'Due date for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint Date Changes',
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
                        $vendor = Vendor::where('id', $complaint->vendor_id)->first();
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $vendor->owner_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Due date for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint Date Changes',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => '',
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
                            'title' => 'Complaint Priority Changes',
                            'body' => 'Priority for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
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
                                'body' => 'Priority for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint Priority Changes',
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
                                'body' => 'Priority for complaint has been changed by ' . $user->role->name . '. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint Priority Changes',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => '',
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
                                'url' => '',
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
                            'title' => 'New Complaint Assigned',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => '',
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
