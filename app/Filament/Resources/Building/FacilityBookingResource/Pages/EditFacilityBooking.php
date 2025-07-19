<?php

namespace App\Filament\Resources\Building\FacilityBookingResource\Pages;

use App\Filament\Resources\Building\FacilityBookingResource;
use App\Models\Building\FacilityBooking;
use App\Models\ExpoPushNotification;
use App\Models\Master\Facility;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditFacilityBooking extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = FacilityBookingResource::class;

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
        $record = $this->record;
        $user = FacilityBooking::where('id', $record->id)->first();
        $facilityName = Facility::where('id', $this->record->bookable_id)->first();
        if ($this->record->approved == 1) {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => $facilityName->name.' Booking Status.',
                        'body' => 'Your amenity booking request for '.$facilityName->name.' is approved',
                        'data' => ['notificationType' => 'MyBookingsFacility',
                                    'building_id'      => $user->building_id,
                                    'flat_id'          => $user->flat_id,],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'custom_json_data' => json_encode([
                            'owner_association_id' => $user->building->owner_association_id ?? 1,
                            'building_id' => $user->building_id ?? null,
                            'flat_id' => $user->flat_id ?? null,
                            'user_id' => $user->user_id ?? null,
                            'type' => 'FacilityBooking',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your amenity booking request for '.$facilityName->name. ' is approved',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' =>  $facilityName->name.' Booking Status.',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id'      => $user->building_id,
                                            'flat_id'          => $user->flat_id,],
                            'format' => 'filament',
                            'url' => 'MyBookingsFacility',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }

        if ($this->record->approved == 0) {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' =>  $facilityName->name.' Booking Status.',
                        'body' => 'Your amenity booking request for '.$facilityName->name. ' is rejected',
                        'data' => ['notificationType' => 'MyBookingsFacility',
                                    'building_id'      => $user->building_id,
                                    'flat_id'          => $user->flat_id,],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'custom_json_data' => json_encode([
                            'owner_association_id' => $user->building->owner_association_id ?? 1,
                            'building_id' => $user->building_id ?? null,
                            'flat_id' => $user->flat_id ?? null,
                            'user_id' => $user->user_id ?? null,
                            'type' => 'FacilityBooking',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your amenity booking request for '.$facilityName->name. ' is rejected',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' =>  $facilityName->name.' Booking Status.',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id'      => $user->building_id,
                                            'flat_id'          => $user->flat_id,],
                            'format' => 'filament',
                            'url' => 'MyBookingsFacility',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }
    }
}
