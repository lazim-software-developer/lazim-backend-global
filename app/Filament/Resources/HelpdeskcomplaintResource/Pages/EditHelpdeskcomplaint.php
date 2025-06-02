<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Jobs\ComplaintStatusMail;
use App\Models\AccountCredentials;
use App\Models\Building\Complaint;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Models\Remark;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EditHelpdeskcomplaint extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = HelpdeskcomplaintResource::class;

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
        if ($this->record->status == 'closed') {
            Complaint::where('id', $this->data['id'])
                ->update([
                    'closed_by'  => auth()->user()->id,
                ]);

            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Facility support issue status',
                        'body' => 'A issue has been resolved by a ' . $role->name . ' ' . auth()->user()->first_name,
                        'data' => ['notificationType' => $this->record->complaint_type == 'help_desk'? 'HelpDeskTabResolved': ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance':'InAppNotficationScreen'),
                        'complaintId'      => $this->record?->id,
                        'open_time' => $this->record?->open_time,
                        'close_time' => $this->record?->close_time,
                        'due_date' => $this->record?->due_date,
                        'building_id' => $this->record->building_id,
                        'flat_id' => $this->record->flat_id
                    ],
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
                            'type' => 'Complaint',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'A issue has been resolved by a ' . $role->name . ' ' . auth()->user()->first_name,
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Facility support issue status',
                            'view' => 'notifications::notification',
                            'viewData' => [
                                'complaintId'      => $this->record?->id,
                                'open_time' => $this->record?->open_time,
                                'close_time' => $this->record?->close_time,
                                'due_date' => $this->record?->due_date,
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
                            ],
                            'format' => 'filament',
                            'url' => $this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance':'HelpDeskTabResolved',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);

                    $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                    $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                    $mailCredentials = [
                        'mail_host' => $credentials->host ?? env('MAIL_HOST'),
                        'mail_port' => $credentials->port ?? env('MAIL_PORT'),
                        'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
                        'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
                        'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                        'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
                    ];
                    $complaintType = "Issue";
                    $remarks = Remark::where('complaint_id',$this->record->id)->get();

                    ComplaintStatusMail::dispatch($this->record->user->email,$this->record->user->first_name,$remarks,$complaintType,$mailCredentials);
        }

        if ($this->record->status == 'in-progress') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Facility support issue status',
                        'body' => 'A issue has been moved to In-progress',
                        'data' => ['notificationType' => $this->record->complaint_type == 'help_desk'? 'HelpDeskTabPending':'InAppNotficationScreen',
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
                        'custom_json_data' => json_encode([
                            'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                            'building_id' => $this->record->building_id ?? null,
                            'flat_id' => $this->record->flat_id ?? null,
                            'user_id' => $this->record->user_id ?? null,
                            'type' => 'Complaint',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'A issue has been moved to In-progress',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Facility support issue status',
                            'view' => 'notifications::notification',
                            'viewData' => ['building_id' => $this->record->building_id,
                                            'flat_id' => $this->record->flat_id],
                            'format' => 'filament',
                            'url' => 'HelpDeskTabPending',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);

        }
    }
}
