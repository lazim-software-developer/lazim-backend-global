<?php

namespace App\Filament\Resources\GuestRegistrationResource\Pages;

use App\Filament\Resources\GuestRegistrationResource;
use App\Models\ExpoPushNotification;
use App\Models\Forms\Guest;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditGuestRegistration extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = GuestRegistrationResource::class;
    protected static ?string $title = 'Guest';

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
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->flatVisitor->initiated_by)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Holiday homes guest registration form status.',
                        'body' => 'Your holiday homes guest registration form has been approved.',
                        'data' => ['notificationType' => 'InAppNotficationScreen',
                                    'building_id' => $this->record->flatVisitor->building_id,
                                    'flat_id' => $this->record->flatVisitor->flat_id],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->flatVisitor->initiated_by,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your holiday homes guest registration form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Holiday homes guest registration form status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->flatVisitor->building_id,
                                    'flat_id' => $this->record->flatVisitor->flat_id],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->flatVisitor->initiated_by)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Holiday homes guest registration form status.',
                        'body' => 'Your holiday homes guest registration form has been rejected.',
                        'data' => ['notificationType' => 'InAppNotficationScreen',
                                    'building_id' => $this->record->flatVisitor->building_id,
                                    'flat_id' => $this->record->flatVisitor->flat_id],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->flatVisitor->initiated_by,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your holiday homes guest registration form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Holiday homes guest registration form status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->flatVisitor->building_id,
                                            'flat_id' => $this->record->flatVisitor->flat_id],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}
