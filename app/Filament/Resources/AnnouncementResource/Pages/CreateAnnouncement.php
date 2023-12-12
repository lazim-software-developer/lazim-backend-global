<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateAnnouncement extends CreateRecord
{
    use UtilsTrait;
    protected static string $resource = AnnouncementResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterCreate()
    {
        $users = FlatTenant::where('active', 1)
            ->where('building_id', $this->data['building_id'])->first();
        $expoPushTokens = ExpoPushNotification::where('user_id', $users->tenant_id)->pluck('token');
        if ($expoPushTokens->count() > 0) {
            foreach ($expoPushTokens as $expoPushToken) {
                $message = [
                    'to' => $expoPushToken,
                    'sound' => 'default',
                    'title' => 'New Announcement',
                    'body' => 'New Announcement',
                    'data' => ['notificationType' => 'app_notification'],
                ];
                $this->expoNotification($message);
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $this->record->user_id,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'New Announcement by ' . auth()->user()->first_name,
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'Residential form Updated!',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament'
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
