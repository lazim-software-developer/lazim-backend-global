<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Community\Post;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Actions;
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
        $users = User::where('active', 1)->first();
        $expoPushTokens = ExpoPushNotification::where('user_id', $users->id)->pluck('token');
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
