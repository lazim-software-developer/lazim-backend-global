<?php

namespace App\Filament\Resources\VisitorFormResource\Pages;

use App\Filament\Resources\VisitorFormResource;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisitorForm extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = VisitorFormResource::class;

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
        if ($this->record->status !== null) 
        {
            if ($this->record->status == 'approved') 
            {
                $security= BuildingPoc::where('building_id',$this->record->building_id)->where('active',true)->first()->user_id;
                $expoPushTokens = ExpoPushNotification::where('user_id', $security)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                $date= $this->record->start_time->date();
                $time= $this->record->time_of_viewing;
                $visitorCount= $this->record->number_of_visitors;
                $unit = $this->record->flat->property_number;
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Visitors',
                        'body' => "Visitors for $date at $time,\n No. of visitors: $visitorCount,\n Unit:$unit ",
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
                }
            }
        }
    }
}
