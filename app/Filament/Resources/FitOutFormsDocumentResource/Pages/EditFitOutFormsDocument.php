<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Models\ExpoPushNotification;
use App\Models\Forms\FitOutForm;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFitOutFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = FitOutFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'FitOut form Updated!',
                        'body' => auth()->user()->first_name . ' approved your fitout form.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
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
                        'title' => 'FitOut form Updated!',
                        'body' => auth()->user()->first_name . ' rejected your fitout form.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                }
            }
        }
    }
}
