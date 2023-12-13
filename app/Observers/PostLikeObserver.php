<?php

namespace App\Observers;

use App\Models\Community\Post;
use App\Models\Community\PostLike;
use App\Models\User\User;
use Filament\Notifications\Notification;

class PostLikeObserver
{
    /**
     * Handle the PostLike "created" event.
     */
    public function created(PostLike $postLike): void
    {
        $post = Post::where('id',$postLike->post_id)->first();
        $notifyTo = User::where('id',$post->user_id)->get();
        Notification::make()
            ->success()
            ->title("Likes")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body(auth()->user()->first_name . 'liked the post!')
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the PostLike "updated" event.
     */
    public function updated(PostLike $postLike): void
    {
        //
    }

    /**
     * Handle the PostLike "deleted" event.
     */
    public function deleted(PostLike $postLike): void
    {
        //
    }

    /**
     * Handle the PostLike "restored" event.
     */
    public function restored(PostLike $postLike): void
    {
        //
    }

    /**
     * Handle the PostLike "force deleted" event.
     */
    public function forceDeleted(PostLike $postLike): void
    {
        //
    }
}
