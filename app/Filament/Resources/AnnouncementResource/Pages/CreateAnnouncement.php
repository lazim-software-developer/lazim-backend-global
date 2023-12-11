<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Community\Post;
use App\Models\ExpoPushNotification;
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
        $post = Post::where('id', $this->record->id)->pluck('user_id');
        $expoPushTokens = ExpoPushNotification::where('user_id', $post->user_id)->pluck('token');
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
