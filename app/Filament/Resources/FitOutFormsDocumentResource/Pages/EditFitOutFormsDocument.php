<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Models\ExpoPushNotification;
use App\Models\Forms\FitOutForm;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditFitOutFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = FitOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            Log::info('userID->>>>>',[$this->record->user_id]);
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            Log::info('expoId->>>>>',[$expoPushTokens]);
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    Log::info('expoId in foreach->>>>>',[$expoPushToken]);
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'FitOut form status',
                        'body' => 'Your FitOut form has been approved.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    Log::info('expoMessage->>>>>',[$message]);
                    $note = $this->expoNotification($message);
                    Log::info('notification->>>>>',[$note]);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your FitOut form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'FitOut form status',
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
        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'FitOut form status',
                        'body' => 'Your FitOut form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);

                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'user',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your FitOut form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'FitOut form status',
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
