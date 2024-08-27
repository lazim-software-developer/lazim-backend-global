<?php

namespace App\Observers;

use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\PostResource;
use App\Models\Community\Post;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class AnnouncementObserver
{
    use UtilsTrait;
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $scheduledAt = Post::where('scheduled_at', now())->get();
        if ($post->status == 'published') {
            foreach ($scheduledAt as $notification) {
                $roles = Role::where('owner_association_id',$post->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
                $notifyTo = User::where('owner_association_id', $post->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
                if ($post->is_announcement) {
                    $requiredPermissions = ['view_any_announcement'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    Notification::make()
                        ->success()
                        ->title("Notice created")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('New Notice has been created.')
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(fn () => PostResource::getUrl('edit', [$post->id])),
                        ])
                        ->sendToDatabase($notifyTo);
                } else {
                    $requiredPermissions = ['view_any_post'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    Notification::make()
                        ->success()
                        ->title("Post created")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('New Post has been created.')
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(fn () => PostResource::getUrl('edit', [$post->id])),
                        ])
                        ->sendToDatabase($notifyTo);
                }
            }
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
