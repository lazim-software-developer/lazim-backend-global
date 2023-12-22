<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
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
        if ($this->record->status == 'published') {
            $building = Building::whereIn('id', $this->data['building_id'])->pluck('owner_association_id');
            $allowedRoles = [1, 11];
            $users = User::whereIn('owner_association_id', $building)->whereIn('role_id', $allowedRoles)->pluck('id');
            // if ($this->record->scheduled_at == now()) {
            foreach ($users as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'url' => 'ComunityPostTab',
                            'title' => 'New Announcement!',
                            'body' => $this->record->content,
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
                                'body' => $this->record->content,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'New Announcement!',
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

            // }
        }
    }
}
