<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterCreate()
    {
        if ($this->record->status == 'published') {
                    $tenant = FlatTenant::where('active',1)
                            ->whereIn('building_id',$this->data['building'])->distinct()->pluck('tenant_id');
                    foreach ($tenant as $user) {
                        $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'url' => 'ComunityPostTab',
                                    'title' => 'New Poll!',
                                    'body' => $this->record->content,
                                    'data' => ['notificationType' => 'ComunityPostTabPoll'],
                                ];
                                $this->expoNotification($message);
                                DB::table('notifications')->insert([
                                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                    'type' => 'Filament\Notifications\DatabaseNotification',
                                    'notifiable_type' => 'App\Models\User\User',
                                    'notifiable_id' => $user,
                                    'data' => json_encode([
                                        'actions' => [],
                                        'body' => $this->record->content,
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
