<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use App\Models\Building\Document;
use App\Models\ExpoPushNotification;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditTenantDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = TenantDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if($data['status'] = 'submitted'){
            $data['status'] = null;
        }
        return $data;
    }

    public function afterSave()
    {
        // If updated value of status is approved
        if ($this->record->status == 'approved') {
            Document::where('id', $this->data['id'])
                ->update([
                    'accepted_by' => auth()->user()->id,
                ]);

            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->documentable_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => $this->record->name . ' Submission Status',
                        'body' => 'The document ' . $this->record->name . ' submitted by you has been ' . $this->record->status . ' by OA admin.',
                        'data' => ['notificationType' => 'MyDocuments'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->documentable_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'The document ' . $this->record->name . ' submitted by you has been ' . $this->record->status . ' by OA admin.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => $this->record->name . ' Submission Status',
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

        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->documentable_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => $this->record->name . ' Submission Status',
                        'body' => 'The document ' . $this->record->name .' submitted by you has been ' . $this->record->status . ' by OA admin.',
                        'data' => ['notificationType' => 'MyDocuments'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->documentable_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'The document ' . $this->record->name . ' submitted by you has been ' . $this->record->status . ' by OA admin.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => $this->record->name . ' Submission Status',
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
