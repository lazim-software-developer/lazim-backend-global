<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Resources\Pages\CreateRecord;

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
            }
        }
    }
}
