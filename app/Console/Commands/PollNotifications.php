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
        $scheduledAt = Poll::whereRaw("DATE_FORMAT(scheduled_at, '%Y-%m-%d %H:%i') = ?", [now()->format('Y-m-d H:i')])
        ->where('status','published')->where('active',true)->get();
        foreach($scheduledAt as $poll){
            $buildings = $poll->building->pluck('id');
            $tenant = FlatTenant::where('active',1)
                    ->whereIn('building_id',$buildings)->distinct()->pluck('tenant_id');
            foreach ($tenant as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        Log::info($expoPushToken);
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'url' => 'ComunityPostTab',
                            'title' => 'New Poll!',
                            'body' => $poll->question,
                            'data' => ['notificationType' =>  'ComunityPostTabPoll' ],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => $poll->question,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Poll!',
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
