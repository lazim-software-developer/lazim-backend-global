<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditMoveOutFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = MoveOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            //notification for who is created the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'MoveOut form status',
                        'body' => 'Your MoveOut form has been approved.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your MoveOut form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'MoveOut form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            //notification for the owners of the building
            $ownerId = Building::where('id', $this->record->building_id)->first();
            $users = User::where('owner_association_id', $ownerId->owner_association_id)->where('role_id', 1)->get();
            foreach ($users as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user->id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'MoveOut form status',
                            'body' => 'Your MoveOut form has been approved.',
                            'data' => ['notificationType' => 'MyRequest'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Your MoveOut form has been approved.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'MoveOut form status',
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

        if ($this->record->status == 'rejected') {
            //notification for who is created the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'MoveOut form status',
                        'body' => 'Your MoveOut form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your MoveOut form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'MoveOut form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            //notification for the owners of the building
            $ownerId = Building::where('id', $this->record->building_id)->first();
            $users = User::where('owner_association_id', $ownerId->owner_association_id)->where('role_id', 1)->get();
            foreach ($users as $user) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $user->id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'MoveOut form status',
                            'body' => 'Your MoveOut form has been rejected.',
                            'data' => ['notificationType' => 'MyRequest'],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'Your MoveOut form has been rejected.',
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'danger',
                                'title' => 'MoveOut form status',
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
