<?php

namespace App\Filament\Resources\PollResource\Pages;

use App\Filament\Resources\PollResource;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePoll extends CreateRecord
{
    use UtilsTrait;
    protected static string $resource = PollResource::class;

    protected function  afterCreate()
    {
        if ($this->record->status == 'published') {
            $tenant = FlatTenant::where('active', 1)
                ->whereIn('building_id', $this->data['building'])->distinct()->pluck('tenant_id');
            foreach ($tenant as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'url' => 'ComunityPostTab',
                            'title' => 'New Poll!',
                            'body' => 'New Poll created',
                            'data' => ['notificationType' => 'ComunityPostTabPoll',
                                    'building_id' => $this->record->building_id],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'New Poll created',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Poll!',
                                'view' => 'notifications::notification',
                                'viewData' => ['building_id' => $this->record->building_id],
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
    }
}
