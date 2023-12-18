<?php

namespace App\Observers;
use App\Models\Community\Post;
use App\Models\User\User;
use Filament\Notifications\Notification;


class AnnouncementObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $notifyTo = User::where('owner_association_id',$post->owner_association_id)->get();
        if($post->is_announcement){
            Notification::make()
                ->success()
                ->title("Announcement created")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New Announcement has been created.')
                ->sendToDatabase($notifyTo);
        }
        else{
            Notification::make()
                ->success()
                ->title("Post created")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New Post has been created.')
                ->sendToDatabase($notifyTo);
        }
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        //
    }
}
