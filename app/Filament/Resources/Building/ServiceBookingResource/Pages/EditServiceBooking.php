<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use App\Filament\Resources\Building\ServiceBookingResource;
use App\Models\ExpoPushNotification;
use App\Models\Master\Service;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditServiceBooking extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ServiceBookingResource::class;

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
        $serviceName = Service::where('id', $this->record->bookable_id)->first();
        if ($this->record->approved == 1) {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => $serviceName->name.' Booking Status.',
                        'body' => 'Your personal service booking request for '.$serviceName->name. ' is approved',
                        'data' => ['notificationType' => 'MyBookingsService',
                                    'building_id'      => $this->record->building_id,
                                    'flat_id'          => $this->record->flat_id],
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
                            'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                            'building_id' => $this->record->building_id ?? null,
                            'flat_id' => $this->record->flat_id ?? null,
                            'user_id' => $this->record->user_id ?? null,
                            'type' => 'ServiceBooking',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your personal service booking request for '.$serviceName->name. ' is approved',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Personal Service Booking Status.',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyBookingsService',
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
                        'title' => $serviceName->name.' Booking Status.',
                        'body' => 'Your personal service booking request for '.$serviceName->name. ' is rejected',
                        'data' => ['notificationType' => 'MyBookingsService',
                                    'building_id'      => $this->record->building_id,
                                    'flat_id'          => $this->record->flat_id],
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
                            'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                            'building_id' => $this->record->building_id ?? null,
                            'flat_id' => $this->record->flat_id ?? null,
                            'user_id' => $this->record->user_id ?? null,
                            'type' => 'ServiceBooking',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your personal service booking request for '.$serviceName->name. ' is rejected',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => $serviceName->name.' Booking Status.',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyBookingsService',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }
    }
}
