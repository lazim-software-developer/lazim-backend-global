<?php

namespace App\Observers;

use App\Models\Building\Complaint;
use App\Models\Community\Comment;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Illuminate\Support\Facades\DB;

class CommentObserver
{
    use UtilsTrait;
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        //Complaints comments by ('Owner', 'Vendor', 'Tenant') these roles then notification will trigger to technician
        if ($comment->commentable_type == 'App\Models\Building\Complaint') {
            $allowedRoles = ['Owner', 'Vendor','Tenant','Security'];
            $user = auth()->user();
            if (in_array($user->role->name, $allowedRoles)) {
                $complaint = Complaint::where('id', $comment->commentable_id)->first();
                if ($complaint->technician_id) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                    if ($complaint->complaint_type == 'snag'){

                        if ($complaint->status == 'open'){
                            $notificationType = 'PendingRequests';
                        }
                        else{
                            $notificationType = 'ResolvedRequests';
                        }
                    }
                    elseif ($complaint->complaint_type == 'help_desk'){
                        if ($complaint->status == 'open'){
                            $notificationType = 'PendingRequests';
                        }
                        else{
                            $notificationType = 'ResolvedRequests';
                        }
                    }
                    else{
                        $notificationType = 'InAppNotficationScreen';
                    }

                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'New Comment',
                                'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                                'data' => ['notificationType' => $notificationType,
                                        'building_id' => $complaint->building_id],
                            ];
                            $this->expoNotification($message);
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $complaint->technician_id,
                                'custom_json_data' => json_encode([
                                    'owner_association_id' => $complaint->building->owner_association_id ?? 1,
                                    'building_id' => $complaint->building_id,
                                    'complaint_id' => $complaint->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'type' => 'Comment',
                                    'priority' => 'Medium',
                                ]),
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New Comment',
                                    'view' => 'notifications::notification',
                                    'viewData' => ['building_id' => $complaint->building_id],
                                    'format' => 'filament',
                                    'url' => $notificationType,
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
                if($complaint->user_id != $comment->user_id){
                    if ($complaint->complaint_type == 'help_desk'){
                        if ($complaint->status == 'open'){
                            $notificationType = 'HelpDeskTabPending';
                        }
                        else{
                            $notificationType = 'HelpDeskTabResolved';
                        }
                    }
                    else{
                        $notificationType = 'InAppNotficationScreen';
                    }
                    if ($complaint->complaint_type == 'snag'){

                        $notificationType = 'MyComplaints';
                    }
                    if ($complaint->complaint_type == 'preventive_maintenance'){

                        $notificationType = 'PreventiveMaintenance';
                    }
                    $expoPushTokens = ExpoPushNotification::where('user_id',  $complaint->user_id)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'title' => 'New Comment',
                                    'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                                    'data' => [
                                        'notificationType' => $notificationType,
                                        'complaintId'      => $complaint?->id,
                                        'open_time' => $complaint?->open_time,
                                        'close_time' => $complaint?->close_time,
                                        'due_date' => $complaint?->due_date,
                                    ],
                                ];
                                $this->expoNotification($message);
                                DB::table('notifications')->insert([
                                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                    'type' => 'Filament\Notifications\DatabaseNotification',
                                    'notifiable_type' => 'App\Models\User\User',
                                    'notifiable_id' => $complaint->user_id,
                                    'custom_json_data' => json_encode([
                                        'owner_association_id' => $complaint->building->owner_association_id ?? 1,
                                        'building_id' => $complaint->building_id,
                                        'complaint_id' => $complaint->id,
                                        'user_id' => auth()->user()->id ?? null,
                                        'type' => 'Comment',
                                        'priority' => 'Medium',
                                    ]),
                                    'data' => json_encode([
                                        'actions' => [],
                                        'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                                        'duration' => 'persistent',
                                        'icon' => 'heroicon-o-document-text',
                                        'iconColor' => 'warning',
                                        'title' => 'New Comment',
                                        'view' => 'notifications::notification',
                                        'viewData' => [
                                            'complaintId'      => $complaint?->id,
                                            'open_time' => $complaint?->open_time,
                                            'close_time' => $complaint?->close_time,
                                            'due_date' => $complaint?->due_date,
                                        ],
                                        'format' => 'filament',
                                        'url' => $notificationType,
                                    ]),
                                    'created_at' => now()->format('Y-m-d H:i:s'),
                                    'updated_at' => now()->format('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                }
            }

            //complaints comment notification who raised the complaint
            if ($user->role->name == 'Technician') {
                $complaint = Complaint::where('id', $comment->commentable_id)->first();
                $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->user_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        if ($complaint->complaint_type == 'help_desk'){
                            if ($complaint->status == 'open'){
                                $notificationType = 'HelpDeskTabPending';
                            }
                            else{
                                $notificationType = 'HelpDeskTabResolved';
                            }
                        }
                        else{
                            $notificationType = 'InAppNotficationScreen';
                        }
                        if ($complaint->complaint_type == 'snag'){

                                $notificationType = 'MyComplaints';
                        }
                        if ($complaint->complaint_type == 'preventive_maintenance'){

                                $notificationType = 'PreventiveMaintenance';
                        }
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'New Comment',
                            'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                            'data' => [
                                'notificationType' => $notificationType,
                                'complaintId'      => $complaint?->id,
                                'open_time' => $complaint?->open_time,
                                'close_time' => $complaint?->close_time,
                                'due_date' => $complaint?->due_date,
                            ],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->user_id,
                            'custom_json_data' => json_encode([
                                'owner_association_id' => $complaint->building->owner_association_id ?? 1,
                                'building_id' => $complaint->building_id,
                                'complaint_id' => $complaint->id,
                                'user_id' => auth()->user()->id ?? null,
                                'type' => 'Comment',
                                'priority' => 'Medium',
                            ]),
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your '.($complaint->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').'. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Comment',
                                'view' => 'notifications::notification',
                                'viewData' => [
                                    'complaintId'      => $complaint?->id,
                                    'open_time' => $complaint?->open_time,
                                    'close_time' => $complaint?->close_time,
                                    'due_date' => $complaint?->due_date,
                                ],
                                'format' => 'filament',
                                'url' => $notificationType,
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        //
    }
}
