<?php

namespace App\Filament\Resources\ComplaintssuggessionResource\Pages;

use App\Filament\Resources\ComplaintssuggessionResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditComplaintssuggession extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ComplaintssuggessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function afterSave()
    {
        if ($this->record->status == 'closed') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'url' => 'InAppNotficationScreen',
                        'title' => 'Suggestion Acknowledgement',
                        'body' => 'You suggestion has been acknowledged by '.auth()->user()->first_name.'. Thank you for your suggestion.',
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
                            'body' => 'You suggestion has been acknowledged by '.auth()->user()->first_name.'. Thank you for your suggestion.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Suggestion Acknowledgement',
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
}
