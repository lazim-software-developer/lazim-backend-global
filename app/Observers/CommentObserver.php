<?php

namespace App\Observers;

use App\Models\Community\Comment;
use App\Models\Community\Post;
use App\Models\User\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $post = Post::where('id',$comment->commentable_id)->first();
        $notifyTo = User::where('id',$post->user_id)->get();
        Notification::make()
        ->success()
        ->title("New comment")
        ->icon('heroicon-o-document-text')
        ->iconColor('warning')
        ->body(auth()->user()->first_name. ' commented on a post.')
        ->sendToDatabase($notifyTo);
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
