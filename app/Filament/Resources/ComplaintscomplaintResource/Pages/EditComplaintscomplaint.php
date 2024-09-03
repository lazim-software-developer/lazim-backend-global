<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Filament\Resources\ComplaintscomplaintResource;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EditComplaintscomplaint extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ComplaintscomplaintResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['type'] = Str::ucfirst($data['type']);
        return $data;
    }

    public function afterSave()
    {
        $role = Role::where('id', auth()->user()->role_id)->first();
        //If complaint is closed by OA admin whoever raised complaint will notify
        if ($this->record->status == 'closed') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Complaint status',
                        'body' => 'Your complaint has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                        'data' => ['notificationType' => 'InAppNotficationScreen'],
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
                            'body' => 'Your complaint has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Complaint status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'InAppNotficationScreen',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);

            if($this->record->technician_id){
                $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->technician_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Complaint status',
                            'body' => 'A complain has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                            'data' => ['notificationType' => 'ResolvedRequests'],
                        ];
                        $this->expoNotification($message);
                    }
                }
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $this->record->technician_id,
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'A complain has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Complaint status',
                                'view' => 'notifications::notification',
                                'viewData' => [],
                                'format' => 'filament',
                                'url' => 'InAppNotficationScreen',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
            }
        }
    }
}
