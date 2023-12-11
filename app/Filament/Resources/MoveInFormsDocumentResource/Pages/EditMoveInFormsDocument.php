<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use App\Models\Building\Document;
use App\Models\ExpoPushNotification;
use App\Models\Forms\MoveInOut;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditMoveInFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = MoveInFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        // If updated value of status is approved
        if ($this->record->status == 'Approved') {
            Document::where('id', $this->data['id'])
                ->update([
                    'accepted_by' => auth()->id(),
                ]);

            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'MoveIn form Updated!',
                        'body' => auth()->user()->first_name . ' approved your MoveIn form.',
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
                        'title' => 'MoveIn form Updated!',
                        'body' => auth()->user()->first_name . ' rejected your MoveIn form.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                }
            }
        }

        $selectedCheckboxes = $this->form->getState('rejected_fields');

        $jsonRejectedFields = json_encode($selectedCheckboxes);

        $this->record->rejected_fields = $jsonRejectedFields;
        $this->record->save();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
