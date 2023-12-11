<?php

namespace App\Filament\Resources\ResidentialFormResource\Pages;

use App\Filament\Resources\ResidentialFormResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResidentialForm extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ResidentialFormResource::class;

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
    // protected function mutateFormDataBeforeFill(array $data): array {
    //     $parkingDetails = json_decode($data['emergency_contact'][0], true);

    //     // $formattedDetails = '';

    //     // if(is_array($parkingDetails)) {
    //     //     foreach($parkingDetails as $key => $val) {
    //     //         // Accumulate the formatted details with line breaks
    //     //         $formattedDetails .= ucfirst(str_replace('_', ' ', $key)).": $val\n";
    //     //     }
    //     // } else {
    //     //     // Handle the case where emergency_contact is not an array
    //     //     $formattedDetails = "Invalid parking details format";
    //     // }

    //     // Assign the accumulated content to $data['emergency_contact']
    //     $data['emergency_contact'] = $parkingDetails;

    //     // Your other logic for data manipulation...

    //     return $data;
    // }

    public function afterSave()
    {
        $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'MoveOut form Updated!',
                        'body' => auth()->user()->first_name . ' approved your MoveOut form.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                }
            }

        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'MoveOut form Updated!',
                        'body' => auth()->user()->first_name . ' rejected your MoveOut form.',
                        'data' => ['notificationType' => 'app_notification'],
                    ];
                    $this->expoNotification($message);
                }
            }
        }
    }
}
