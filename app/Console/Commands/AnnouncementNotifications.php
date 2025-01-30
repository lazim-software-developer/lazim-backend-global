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
        try {
            date_default_timezone_set('Asia/Dubai');
            $currentTime = now();

            $query = Post::whereRaw("DATE(scheduled_at) = ? AND TIME_FORMAT(TIME(scheduled_at), '%H:%i') = ?", [
                $currentTime->format('Y-m-d'),
                $currentTime->format('H:i')
            ])
            ->where('status', 'published')
            ->where('active', true);

            $scheduledAt = $query->get();

            if ($scheduledAt->count() === 0) {
                return;
            }

            $totalNotificationsSent = 0;

            foreach ($scheduledAt as $post) {
                $buildingIds = $post->building->pluck('id')->toArray();

                $tenant = FlatTenant::where('active', 1)
                    ->whereIn('building_id', $buildingIds)
                    ->distinct()
                    ->pluck('tenant_id');

                foreach ($tenant as $user) {
                    try {
                        // Create database notification
                        $notificationData = [
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => strip_tags($post->content),
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => $post->is_announcement ? 'New Notice!' : 'New Post!',
                                'view' => 'notifications::notification',
                                'viewData' => [
                                    'building_id' => $buildingIds,
                                ],
                                'format' => 'filament',
                                'url' => $post->is_announcement ? 'ComunityPostTabNotice' : 'ComunityPostTabPost',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ];

                        DB::beginTransaction();
                        DB::table('notifications')->insert($notificationData);
                        DB::commit();

                        $totalNotificationsSent++;

                        // Handle push notifications if tokens exist
                        $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'url' => 'ComunityPostTab',
                                    'title' => $post->is_announcement ? 'New Notice!' : 'New Post!',
                                    'body' => strip_tags($post->content),
                                    'data' => [
                                        'notificationType' => $post->is_announcement ? 'ComunityPostTabNotice' : 'ComunityPostTabPost',
                                        'building_id' => $buildingIds,
                                    ],
                                ];
                                $this->expoNotification($message);
                            }
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to process notification:', [
                            'user_id' => $user,
                            'post_id' => $post->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Announcement notification command failed: ' . $e->getMessage());
        }
    }
}
