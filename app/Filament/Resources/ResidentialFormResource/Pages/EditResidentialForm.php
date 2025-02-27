<?php

namespace App\Filament\Resources\ResidentialFormResource\Pages;

use App\Filament\Resources\ResidentialFormResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditResidentialForm extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ResidentialFormResource::class;
    protected static ?string $title = 'Residential';
    protected static ?string $modelLabel = 'Residential';

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
        if ($this->record->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Residential form status',
                        'body' => 'Your residential form has been approved.',
                        'data' => ['notificationType' => 'MyRequest',
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your residential form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Residential form status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }

        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Residential form status',
                        'body' => 'Your residential form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest',
                                    'building_id' => $this->record->building_id,
                                    'flat_id' => $this->record->flat_id],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your residential form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Residential form status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array {
        $emergencyContact = json_decode($data['emergency_contact'], true);
        $formattedDetails = '';

        if(is_array($emergencyContact)) {
            $details = [
                'name'=> $emergencyContact[0]['name'],
                'phone'=>$emergencyContact['phone'] = $emergencyContact[0]['country'].$emergencyContact[0]['phone']
            ];
            foreach($details as $key => $val) {
                // Accumulate the formatted details with line breaks
                $formattedDetails .= ucfirst(str_replace('_', ' ', $key)).": $val\n";
            }
        } else {
            // Handle the case where emergency contact is not an array
            $formattedDetails = "Invalid emergency contact format";
        }

        // Assign the accumulated content to $data['emergency_contact']
        $data['emergency_contact'] = $formattedDetails;

        // Your other logic for data manipulation...

        return $data;
    }

}
