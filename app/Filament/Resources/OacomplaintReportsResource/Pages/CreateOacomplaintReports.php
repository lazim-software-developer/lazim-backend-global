<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOacomplaintReports extends CreateRecord
{
    use UtilsTrait;

    protected static string $resource = OacomplaintReportsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ticket_number'] = generate_ticket_number('OC');
        $data['due_date'] = now()->addDay(3);
        $data['open_time'] = now();
        $data['status'] = 'open';
        $data['priority'] = 1;
        return $data;
    }

    protected function afterCreate()
    {
        $user_id = $this->record?->user_id;
        $code = '';
        if($this->record?->type == 'Technician'){
            $user_id = $this->record?->technician_id;
            $code = 'PendingRequests';
        }
        elseif($this->record?->type == 'Vendor'){
            $user_id = $this->record?->vendor_id;
            $code = 'task';
        }
        elseif($this->record?->type == 'Gatekeeper'){
            $user_id = $this->record?->user_id; 
            $code = 'AssignedToMe';
        }
        $expoPushToken = ExpoPushNotification::where('user_id', $user_id)->first()?->token;
        if ($expoPushToken) {
            $message = [
                'to'    => $expoPushToken,
                'sound' => 'default',
                'title' => 'Task Assigned',
                'body'  => 'Task has been assigned',
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
                'body'      => 'Task has been assigned',
                'duration'  => 'persistent',
                'icon'      => 'heroicon-o-document-text',
                'iconColor' => 'warning',
                'title'     => 'Task Assigned',
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
