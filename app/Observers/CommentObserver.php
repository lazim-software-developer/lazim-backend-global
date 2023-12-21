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
            $allowedRoles = ['Owner', 'Vendor','Tenant'];
            $user = auth()->user();
            if (in_array($user->role->name, $allowedRoles)) {
                $complaint = Complaint::where('id', $comment->commentable_id)->first();
                if ($complaint->technician_id) {
                    $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->technician_id)->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'New Comment',
                                'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your complaint. Check the application for the infomation.',
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
                                    'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your complaint. Check the application for the infomation.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'New Comment',
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

            //complaints comment notification who raised the complaint
            if ($user->role->name == 'Technician') {
                $complaint = Complaint::where('id', $comment->commentable_id)->first();
                $expoPushTokens = ExpoPushNotification::where('user_id', $complaint->user_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'New Comment',
                            'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your complaint. Check the application for the infomation.',
                            'data' => ['notificationType' => 'app_notification'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $complaint->user_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Comment made by '.$user->role->name.' '.$user->first_name.' on your complaint. Check the application for the infomation.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Comment',
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
