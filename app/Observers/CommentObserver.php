<?php

namespace App\Observers;

use App\Models\Building\Complaint;
use App\Models\Community\Comment;
use App\Models\Community\Post;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CommentObserver
{
    use UtilsTrait;
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        //Post comment notification for admin
        $post = Post::where('id', $comment->commentable_id)->first();
        $notifyTo = User::where('id', $post->user_id)->get();
        Notification::make()
            ->success()
            ->title("New comment")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body(auth()->user()->first_name .' commented on a post.')
            ->sendToDatabase($notifyTo);

        //Complaints comments by ('Owner', 'Vendor', 'Tenant') these roles then notification will trigger to technician
        if ($comment->commentable_type == 'App\Models\Building\Complaint') {
            $allowedRoles = ['Owner', 'Vendor', 'Tenant'];
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
                                'body' => 'Comment made on ',
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
                                    'body' => 'Comment made on ',
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
