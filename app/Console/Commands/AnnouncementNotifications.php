<?php

namespace App\Console\Commands;

use App\Models\Building\FlatTenant;
use App\Models\Community\Post;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementNotifications extends Command
{
    use UtilsTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:announcement-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Announcement and Post notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduledAt = Post::whereRaw("DATE_FORMAT(scheduled_at, '%Y-%m-%d %H:%i') = ?", [now()->format('Y-m-d H:i')])
        ->where('status','published')->get();
        Log::info($scheduledAt);
        foreach($scheduledAt as $post){
            $buildings = $post->building->pluck('id');
            Log::info($buildings);
            $tenant = FlatTenant::where('active',1)
                    ->whereIn('building_id',$buildings)->distinct()->pluck('tenant_id');
                    Log::info($tenant);
            foreach ($tenant as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                Log::info($expoPushTokens);
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        Log::info($expoPushToken);
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'url' => 'ComunityPostTab',
                            'title' => 'New '. $post->is_announcement ? 'Announcement!' : 'Post!',
                            'body' => $post->content,
                            'data' => ['notificationType' => 'ComunityPostTabNotice'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => $post->content,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New '. $post->is_announcement ? 'Announcement!' : 'Post!',
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
