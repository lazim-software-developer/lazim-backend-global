<?php

namespace App\Observers;

use App\Models\User\User;
use App\Traits\UtilsTrait;
use App\Models\Master\Role;
use App\Models\Community\Post;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\PostResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\AnnouncementResource;

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
                $roles = Role::where('owner_association_id',$post->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
                $notifyTo = User::where('owner_association_id', $post->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
                if ($post->is_announcement) {
                    $requiredPermissions = ['view_any_announcement'];
                    $notifyTo->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    if($notifyTo->count() > 0){
                        foreach($notifyTo as $user){
                            if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->post_id', $post->id)->exists()){
                                $data=[];
                                $data['notifiable_type']='App\Models\User\User';
                                $data['notifiable_id']=$user->id;
                                $slug = OwnerAssociation::where('id',$post->owner_association_id)->first()?->slug;
                                if($slug){
                                    $data['url']=AnnouncementResource::getUrl('edit', [$slug, $post?->id]);
                                }else{
                                    $data['url']=url('/app/announcements/' . $post?->id.'/edit');
                                }
                                $data['title']='Notice Created';
                                $data['body']='New Notice has been created by ' . auth()->user()->first_name;
                                $data['building_id']=$post->building_id;
                                $data['custom_json_data']=json_encode([
                                    'building_id' => $post->building_id,
                                    'post_id' => $post->id,
                                    'user_id' => auth()->user()->id ?? null,
                                    'owner_association_id' => $post->owner_association_id,
                                    'type' => 'Announcement',
                                    'priority' => 'Medium',
                                ]);
                                NotificationTable($data);
                            }
                        }
                    }
                    // Notification::make()
                    //     ->success()
                    //     ->title("Notice Created")
                    //     ->icon('heroicon-o-document-text')
                    //     ->iconColor('warning')
                    //     ->body('New Notice has been created.')
                    //     ->actions([
                    //         Action::make('view')
                    //             ->button()
                    //             ->url(function() use ($post){
                    //                 $slug = OwnerAssociation::where('id',$post->owner_association_id)->first()?->slug;
                    //                 if($slug){
                    //                     return AnnouncementResource::getUrl('edit', [$slug,$post?->id]);
                    //                 }
                    //                 return url('/app/announcements/' . $post?->id.'/edit');
                    //             }),
                    //     ])
                    //     ->sendToDatabase($notifyTo);
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
                                ->url(function() use ($post){
                                    $slug = OwnerAssociation::where('id',$post->owner_association_id)->first()?->slug;
                                    if($slug){
                                        return PostResource::getUrl('edit', [$slug,$post?->id]);
                                    }
                                    return url('/app/posts/' . $post?->id.'/edit');
                                }),
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
