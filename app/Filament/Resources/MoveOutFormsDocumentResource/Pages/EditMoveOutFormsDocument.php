<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditMoveOutFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = MoveOutFormsDocumentResource::class;
    protected static ?string $title = 'Move out';
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

    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            //notification for who is created the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Move-out form status',
                        'body' => 'Your move-out form has been approved.',
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
                            'body' => 'Your move-out form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Move-out form status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                $security= $this->record->building->buildingPocs->where('active',true)->where('role_name','security')->first();
                if($security?->exists()) {
                    $id = $security?->first()?->user_id;
                    $expoPushTokens = ExpoPushNotification::where('user_id', $id)->pluck('token');
                    if ($expoPushTokens->count() > 0) {
                        foreach ($expoPushTokens as $expoPushToken) {
                            $message = [
                                'to' => $expoPushToken,
                                'sound' => 'default',
                                'title' => 'Move-out',
                                'body' => 'New move-out form received.',
                                'data' => ['notificationType' => 'Move-out'],
                            ];
                            $this->expoNotification($message);
                        }
                    }
                            DB::table('notifications')->insert([
                                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                'type' => 'Filament\Notifications\DatabaseNotification',
                                'notifiable_type' => 'App\Models\User\User',
                                'notifiable_id' => $id,
                                'data' => json_encode([
                                    'actions' => [],
                                    'body' => 'New move-out form received.',
                                    'duration' => 'persistent',
                                    'icon' => 'heroicon-o-document-text',
                                    'iconColor' => 'warning',
                                    'title' => 'Move-out',
                                    'view' => 'notifications::notification',
                                    'viewData' => [],
                                    'format' => 'filament',
                                    'url' => 'Move-out',
                                ]),
                                'created_at' => now()->format('Y-m-d H:i:s'),
                                'updated_at' => now()->format('Y-m-d H:i:s'),
                            ]);
                }
        }

        if ($this->record->status == 'rejected') {
            //notification for who is created the form
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Move-out form status',
                        'body' => 'Your move-out form has been rejected.',
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
                            'body' => 'Your move-out form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Move-out form status',
                            'view' => 'notifications::notification',
                            'viewData' => [ 'building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }

        if ($this->record->rejected_fields){
            $rejectedFieldsJson = json_encode(['rejected_fields' => $this->record->rejected_fields]);
            $this->record->update(['rejected_fields' =>  $rejectedFieldsJson]);
            $this->record->save();
        }
    }
}
