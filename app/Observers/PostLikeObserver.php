<?php

namespace App\Observers;

use App\Filament\Resources\PostResource;
use App\Models\Community\Post;
use App\Models\Community\PostLike;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PostLikeObserver
{
    /**
     * Handle the PostLike "created" event.
     */
    public function created(PostLike $postLike): void
    {
        $requiredPermissions = ['view_any_post'];
        $post = Post::where('id',$postLike->post_id)->first();
        $notifyTo = User::where('id',$post->user_id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        $building_id = DB::table('building_post')->where('post_id', $postLike->post_id)->first()->building_id;
        $oam_id = DB::table('building_owner_association')->where('building_id', $building_id)->where('active', true)->first();
        Notification::make()
            ->success()
            ->title("Likes")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body(auth()->user()->first_name . ' liked the post!')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(function() use ($oam_id,$post){
                        $slug = OwnerAssociation::where('id',$oam_id->owner_association_id)->first()?->slug;
                        if($slug){
                            return PostResource::getUrl('edit', [$slug,$post?->id]);
                        }
                        return url('/app/posts/' . $post?->id.'/edit');
                    }),
            ])
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
