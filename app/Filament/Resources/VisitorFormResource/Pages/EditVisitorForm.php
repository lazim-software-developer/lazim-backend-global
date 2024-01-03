<?php

namespace App\Filament\Resources\VisitorFormResource\Pages;

use App\Filament\Resources\VisitorFormResource;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditVisitorForm extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = VisitorFormResource::class;
    protected static ?string $title = 'Flat visitor';

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
                $date= $this->record->start_time->toDateString();
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
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $security,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => "Visitors for $date at $time,\n No. of visitors: $visitorCount,\n Unit:$unit ",
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Visitors',
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
}
