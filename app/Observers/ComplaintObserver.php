<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Traits\UtilsTrait;
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
        $notifyTo = User::where('owner_association_id', $complaint->owner_association_id)->where('role_id', 10)->get();
        if ($complaint->complaint_type == 'tenant_complaint') {

            Notification::make()
                ->success()
                ->title("Happiness center Complaint")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('Complaint has been created by' . auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        } elseif ($complaint->complaint_type == 'enquiries') {
            Notification::make()
                ->success()
                ->title("New Enquiry Received")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A enquiry has been received raised by ' . auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        } elseif ($complaint->complaint_type == 'suggestions') {
            Notification::make()
                ->success()
                ->title("New Suggestion Received")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A suggestion made by ' . auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        } else {
            Notification::make()
                ->success()
                ->title("Help Desk Ticket ")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('A new Ticket is raised by ' . auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
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
        $building = Building::where('id',$complaint->building_id )->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
        //DB notification for ADMIN status update from resident/technician
        if($complaint->status == 'closed'){
            if ($complaint->complaint_type == 'help_desk') {
                Notification::make()
                    ->success()
                    ->title("Help Desk Complaint Resolution ")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('Complaint has been resolved by a '.$user->role->name.' '.auth()->user()->first_name)
                    ->sendToDatabase($notifyTo);
            } else {
                Notification::make()
                    ->success()
                    ->title("Complaints Resolved")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('Complaint has been resolved by a '.$user->role->name.' '.auth()->user()->first_name)
                    ->sendToDatabase($notifyTo);
                }
            }

        //assign technician notification to assigned technician (assigned by 'OA', 'Vendor')
        $allowedRole = ['OA', 'Vendor'];
        if (in_array($user->role->name, $allowedRole)){
            // if (!$complaint->technician_id) {
            //     $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
            //     if ($expoPushTokens->count() > 0) {
            //         foreach ($expoPushTokens as $expoPushToken) {
            //             $message = [
            //                 'to' => $expoPushToken,
            //                 'sound' => 'default',
            //                 'title' => 'New Complaint Assigned',
            //                 'body' => 'A new complaint assigned to you.',
            //                 'data' => ['notificationType' => 'app_notification'],
            //             ];
            //             $this->expoNotification($message);
            //             DB::table('notifications')->insert([
            //                 'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            //                 'type' => 'Filament\Notifications\DatabaseNotification',
            //                 'notifiable_type' => 'App\Models\User\User',
            //                 'notifiable_id' => $complaint->technician_id,
            //                 'data' => json_encode([
            //                     'actions' => [],
            //                     'body' => 'A new complaint assigned to you.',
            //                     'duration' => 'persistent',
            //                     'icon' => 'heroicon-o-document-text',
            //                     'iconColor' => 'warning',
            //                     'title' => 'New Complaint Assigned',
            //                     'view' => 'notifications::notification',
            //                     'viewData' => [],
            //                     'format' => 'filament',
            //                 ]),
            //                 'created_at' => now()->format('Y-m-d H:i:s'),
            //                 'updated_at' => now()->format('Y-m-d H:i:s'),
            //             ]);
            //         }
            //     }
            // }

            //if technician updated then older technician will notify
            if($newValues['technician_id'] != $oldValues['technician_id']){
                $expoPushTokens = ExpoPushNotification::where('user_id', $oldValues['technician_id'])->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Complaint Assignment Status',
                            'body' => 'You have been relived from the complaint by the vendor.',
                            'data' => ['notificationType' => 'app_notification'],
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
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            //if technician updated then new technician will notify
            if($newValues['technician_id'] != $oldValues['technician_id']){
                $expoPushTokens = ExpoPushNotification::where('user_id', $newValues['technician_id'])->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'New Complaint Assigned',
                            'body' => 'A new complaint assigned to you.',
                            'data' => ['notificationType' => 'app_notification'],
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
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        //complaints status update push notification to technician mobile app (if closed by 'Owner', 'OA', 'Tenant')
        $allowedRoles = ['Owner', 'OA', 'Tenant'];
        if (in_array($user->role->name, $allowedRoles)) {
            if($complaint->status == 'closed'){
                if ($complaint->complaint_type == 'help_desk') {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'Help Desk complaint status',
                                'body' => 'A complaint has been resolved by a '.$user->role->name.' '.auth()->user()->first_name,
                                'data' => ['notificationType' => 'HelpDeskTab'],
                            ];
                            $this->expoNotification($message);
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $complaint->technician_id,
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'A complaint has been resolved by a '.$user->role->name.' '.auth()->user()->first_name,
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'Help Desk complaint status',
                                    'view' => 'notifications::notification',
                                    'viewData' => [],
                                    'format' => 'filament',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }

        }

        //if due_date updated then assign technician will get the notification
        if($newValues['due_date'] != $oldValues['due_date']){
            $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Complaint Date Changes',
                        'body' => 'Due date for complaint has been changed by vendor. Check the application for the infomation.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $complaint->technician_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Due date for complaint has been changed by vendor. Check the application for the infomation.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Complaint Date Changes',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        //if priority updated then assign technician will get the notification
        if($newValues['priority'] != $oldValues['priority']){
            $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Complaint Priority Changes',
                        'body' => 'Priority for complaint has been changed by vendor. Check the application for the infomation.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $complaint->technician_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Priority for complaint has been changed by vendor. Check the application for the infomation.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Complaint Priority Changes',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
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
