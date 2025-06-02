<?php

namespace App\Console\Commands;

use App\Models\Building\FlatTenant;
use App\Models\Community\Poll;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PollNotifications extends Command
{
    use UtilsTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:poll-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduledAt = Poll::whereBetween('scheduled_at', [
            now()->subMinute()->startOfMinute(),
            now()->startOfMinute()
        ])
        ->where('status','published')->where('active',true)->distinct()->get();

        $buildings=DB::table('building_poll')
            ->whereIn('poll_id',$scheduledAt->pluck('id'))
            ->distinct()
            ->pluck('building_id');
            $tenant = FlatTenant::where('active',1)
                    ->whereIn('building_id',$buildings)->distinct()->pluck('tenant_id');

            foreach ($tenant as $user) {
                $expoPushToken = ExpoPushNotification::where('user_id', $user)->first()?->token;
                if ($expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'url' => 'ComunityPostTab',
                            'title' => 'New Poll!',
                            'body' => 'New Poll launched',
                            'data' => ['notificationType' =>  'ComunityPostTabPoll',
                                        'building_id' => $buildings,
                            ],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user,
                            'custom_json_data' => json_encode([
                                'owner_association_id' => $buildings->first()->owner_association_id ?? 1,
                                'building_id' => $buildings,
                                'user_id' => $user,
                                'type' => 'Poll',
                                'priority' => 'Medium',
                            ]),
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'New Poll launched',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Poll!',
                                'view' => 'notifications::notification',
                                'viewData' => ['building_id'=>$buildings],
                                'format' => 'filament',
                                'url' => 'ComunityPostTabPoll',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
            }
        }
    }
}
