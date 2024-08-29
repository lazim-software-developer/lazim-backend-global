<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditOacomplaintReports extends EditRecord
{
    use UtilsTrait;

    protected static string $resource = OacomplaintReportsResource::class;

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
        $type = $this->record->type;

        if ($type == 'Gatekeeper') {
            $data['user_id'] = $this->record->user_id;
        } elseif ($type == 'Vendor') {
            $data['user_id'] = $this->record->vendor_id;
        } elseif ($type == 'Technician') {
            $data['user_id'] = $this->record->technician_id;
        }

        return $data;
    }

    protected function beforeSave()
    {
        $user_id = $this->record?->user_id;
        $code = '';
        if ($this->record->status != 'closed') {
            if ($this->record?->type == 'Technician' && $this->record->status != 'closed') {
                $user_id = $this->record?->technician_id;
                $code = 'ResolvedRequests';
            } elseif ($this->record?->type == 'Vendor') {
                $user_id = $this->record?->vendor_id;
                $code = '';
            } elseif ($this->record?->type == 'Gatekeeper') {
                $user_id = $this->record?->user_id;
                $code = 'AssignedToMe';
            }
            $expoPushToken = ExpoPushNotification::where('user_id', $user_id)->first()?->token;
            if ($expoPushToken) {
                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'title' => 'Complaint closed',
                    'body'  => 'Complaint closed by OA.',
                    'data'  => ['notificationType' => $code],
                ];
                $this->expoNotification($message);
            }
            DB::table('notifications')->insert([
                'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type'            => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id'   => $user_id,
                'data'            => json_encode([
                    'actions'   => [],
                    'body'      => 'Complaint closed by OA.',
                    'duration'  => 'persistent',
                    'icon'      => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title'     => 'Complaint closed',
                    'view'      => 'notifications::notification',
                    'viewData'  => [],
                    'format'    => 'filament',
                    'url'       => $code,
                ]),
                'created_at'      => now()->format('Y-m-d H:i:s'),
                'updated_at'      => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
