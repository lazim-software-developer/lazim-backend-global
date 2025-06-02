<?php

namespace App\Filament\Resources\SnagsResource\Pages;

use App\Filament\Resources\SnagsResource;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditSnags extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = SnagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
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
                        'title' => 'Snags status',
                        'body' => 'Your Snag has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                        'data' => ['notificationType' => 'MyComplaints',
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id],
                    ];
                    $this->expoNotification($message);
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
                            'type' => 'Proposal',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your Snag has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Snags status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->flatVisitor?->building_id ?? $this->record->building_id,
                                            'flat_id' => $this->record->flatVisitor?->flat_id ?? $this->record->flat_id,],
                            'format' => 'filament',
                            'url' => 'MyComplaints',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
            if($this->record->technician_id){
                $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->technician_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => 'Snags status',
                            'body' => 'A Snag has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                            'data' => ['notificationType' => 'ResolvedRequests',
                                    'building_id' => $this->record->building_id,
                                    'flat_id' => $this->record->flat_id],
                        ];
                        $this->expoNotification($message);
                        DB::table('notifications')->insert([
                            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => 'App\Models\User\User',
                            'notifiable_id' => $this->record->technician_id,
                            'custom_json_data' => json_encode([
                                'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                                'building_id' => $this->record->building_id ?? null,
                                'flat_id' => $this->record->flat_id ?? null,
                                'user_id' => $this->record->user_id ?? null,
                                'type' => 'Proposal',
                                'priority' => 'Medium',
                            ]),
                            'data' => json_encode([
                                'actions' => [],
                                'body' => 'A Snag has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                                'duration' => 'persistent',
                                'icon' => 'heroicon-o-document-text',
                                'iconColor' => 'warning',
                                'title' => 'Snags status',
                                'view' => 'notifications::notification',
                                'viewData' => ['building_id'=> $this->record->building_id,
                                                'flat_id' => $this->record->flat_id],
                                'format' => 'filament',
                                'url' => 'ResolvedRequests',
                            ]),
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
