<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Models\Building\FlatTenant;
use App\Models\ExpoPushNotification;
use App\Models\Master\Service;
use App\Traits\UtilsTrait;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateComplaint extends CreateRecord
{
    use UtilsTrait;

    protected static string $resource = ComplaintResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $serviceName = Service::where('id', $data['service_id'])->value('name');

        $data['priority']             = 3;
        $data['status']               = 'open';
        $data['complaintable_type']   = 'App\Models\Vendor\Vendor';
        $data['complaintable_id']     = auth()->user()->id;
        $data['user_id']              = auth()->user()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;
        $data['category']             = $serviceName;
        $data['ticket_number']        = generate_ticket_number("CP");
        $data['complaint_type']       = 'preventive_maintenance';
        $data['open_time']            = Carbon::now();
        return $data;
    }

    protected function afterCreate(): void
    {
        $complaint = $this->record;

        // Save media files
        if ($this->data['media'] ?? null) {
            foreach ($this->data['media'] as $file) {
                $complaint->media()->create([
                    'name' => 'before',
                    'url'  => $file,
                ]);
            }
        }

        $residentIds = FlatTenant::where([
            'building_id' => $complaint->building_id,
            'active'      => true,
        ])->distinct()->pluck('tenant_id');

        if ($residentIds->count() > 0) {
            foreach ($residentIds as $residentId) {
                $pushNotification = ExpoPushNotification::where('user_id', $residentId)->first();

                // Add this debug line
                \Log::info('Testing notification token', [
                    'resident_id' => $residentId,
                    'token'       => $pushNotification?->token ?? 'No token found',
                ]);

                if ($pushNotification && $pushNotification->token) {
                    $token = trim($pushNotification->token);

                    // Validate token format
                    if (!str_starts_with($token, 'ExponentPushToken[')) {
                        \Log::error('Invalid token format', [
                            'resident_id' => $residentId,
                            'token'       => $token,
                        ]);
                        continue;
                    }

                    try {
                        // Log token for debugging
                        \Log::info('Attempting to send notification', [
                            'resident_id' => $residentId,
                            'token'       => $token,
                        ]);

                        $message = [
                            'to'    => $token,
                            'sound' => 'default',
                            'title' => 'Preventive Maintenance',
                            'body'  => 'A preventive maintenance has been scheduled for your building',
                            'data'  => [
                                'notificationType' => 'PreventiveMaintenance',
                                'complaintId'      => $complaint?->id,
                                'open_time' => $complaint?->open_time,
                                'close_time' => $complaint?->close_time,
                                'due_date' => $complaint?->due_date,
                            ],
                        ];

                        $response = $this->expoNotification($message);

                        // Log response for debugging
                        \Log::info('Notification response', [
                            'response'    => $response,
                            'resident_id' => $residentId,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Expo notification failed', [
                            'error'       => $e->getMessage(),
                            'resident_id' => $residentId,
                            'token'       => $token,
                        ]);
                    }
                }

                // Continue with database notification...
                DB::table('notifications')->insert([
                    'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type'            => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id'   => $residentId,
                    'data'            => json_encode([
                        'actions'   => [],
                        'body'      => 'A preventive maintenance has been scheduled for your building',
                        'duration'  => 'persistent',
                        'icon'      => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title'     => 'Preventive Maintenance',
                        'view'      => 'notifications::notification',
                        'viewData'  => [
                                'complaintId'      => $complaint?->id,
                                'open_time' => $complaint?->open_time,
                                'close_time' => $complaint?->close_time,
                                'due_date' => $complaint?->due_date,
                        ],
                        'format'    => 'filament',
                        'url'       => 'PreventiveMaintenance',
                    ]),
                    'created_at'      => now()->format('Y-m-d H:i:s'),
                    'updated_at'      => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
