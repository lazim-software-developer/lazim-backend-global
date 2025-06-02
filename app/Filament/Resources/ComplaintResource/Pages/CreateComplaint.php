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

        switch ($serviceName) {
            case 'Cleaning Common Area':
                $serviceName = 'House Keeping';
                break;
            case 'Security Services':
                $serviceName = 'Security';
                break;
            case 'MEP Services':
                $serviceName = 'Electrical';
                break;
            case 'Pumps And Motors':
                $serviceName = 'Plumbing';
                break;
            case 'Sliding/Revolving And Exit Doors':
                $serviceName = 'AC';
                break;
            case 'General Pest Control Service':
                $serviceName = 'Pest Control';
                break;
            case 'Other':
                $serviceName = 'Other';
                break;
            default:
                $serviceName = 'Other';
                break;
        }

        // Set category based on serviceName
        switch ($serviceName) {
            case 'House Keeping':
                $category = 'Cleaning Common Area';
                break;
            case 'Security':
                $category = 'Security Services';
                break;
            case 'Electrical':
            case 'Plumbing':
            case 'AC':
                $category = 'MEP Services';
                break;
            case 'Pest Control':
                $category = 'General Pest Control Service';
                break;
            default:
                $category = 'Other';
                break;
        }

        // Map the service names back to their original IDs
        $serviceIdMap = [
            'Electrical'    => 69,
            'Plumbing'      => 69,
            'AC'            => 69,
        ];

        $data['priority']             = 3;
        $data['status']               = 'open';
        $data['complaintable_type']   = 'App\Models\Vendor\Vendor';
        $data['complaintable_id']     = auth()->user()->id;
        $data['user_id']              = auth()->user()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;
        $data['category']             = $category;
        $data['ticket_number']        = generate_ticket_number("CP");
        $data['complaint_type']       = 'preventive_maintenance';
        $data['open_time']            = Carbon::now();
        $data['selected_service']     = $serviceName;

        // Save the correct service_id based on the service name
        if (isset($serviceIdMap[$serviceName])) {
            $data['service_id'] = $serviceIdMap[$serviceName];
        }

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
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
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
                    'custom_json_data' => json_encode([
                        'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                        'building_id' => $this->record->building_id ?? null,
                        'flat_id' => $this->record->flat_id ?? null,
                        'user_id' => $this->record->user_id ?? null,
                        'type' => 'Complaint',
                        'priority' => 'Medium',
                    ]),
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
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
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
