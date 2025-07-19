<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use Filament\Actions;
use App\Models\Remark;
use App\Traits\UtilsTrait;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use App\Models\OwnerAssociation;
use App\Jobs\ComplaintStatusMail;
use App\Models\AccountCredentials;
use Illuminate\Support\Facades\DB;
use App\Models\ExpoPushNotification;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ComplaintscomplaintResource;

class EditComplaintscomplaint extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ComplaintscomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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
        $data['closed_by'] = auth()->check() ? auth()->user()->id : null;

        return $data;
    }

    public function beforeSave()
    {
        $data = $this->form->getState();

        if ((array_key_exists('remarks', $data) && $data['remarks'] != $this->record->remarks) || (array_key_exists('status', $data) && $data['status'] != $this->record->status)) {

            Remark::create([
                'remarks' => json_encode($data['remarks']),
                'type' => 'Complaint',
                'status' => $data['status'],
                'user_id' => auth()->user()->id,
                'complaint_id' => $this->record->id,
            ]);
        }
    }

    public function afterSave() ### TODO Change for the notification
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
                        'title' => ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' status',
                        'body' => ('Your ' . $this->record->complaint_type === 'preventive_maintenance'
                            ? 'PreventiveMaintenance'
                            : 'complaint') . ' has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
                        'data' => [
                            'notificationType' => $this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'InAppNotficationScreen',
                            'complaintId'      => $this->record?->id,
                            'open_time' => $this->record?->open_time,
                            'close_time' => $this->record?->close_time,
                            'due_date' => $this->record?->due_date,
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id
                        ],
                    ];
                    $this->expoNotification(message: $message);
                }
            }
            // DB::table('notifications')->insert([
            //     'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            //     'type' => 'Filament\Notifications\DatabaseNotification',
            //     'notifiable_type' => 'App\Models\User\User',
            //     'notifiable_id' => $this->record->user_id,
            //     'data' => json_encode([
            //         'actions' => [],
            //         'body' => ('Your '.$this->record->complaint_type === 'preventive_maintenance'
            //                 ? 'PreventiveMaintenance'
            //                 : 'complaint').' has been resolved by '.$role->name.' : '.auth()->user()->first_name,
            //         'duration' => 'persistent',
            //         'icon' => 'heroicon-o-document-text',
            //         'iconColor' => 'warning',
            //         'title' => ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' status',
            //         'view' => 'notifications::notification',
            //         'viewData' => [
            //             'complaintId'      => $this->record?->id,
            //             'open_time' => $this->record?->open_time,
            //             'close_time' => $this->record?->close_time,
            //             'due_date' => $this->record?->due_date,
            //             'building_id' => $this->record->building_id,
            //             'flat_id' => $this->record->flat_id
            //         ],
            //         'format' => 'filament',
            //         'url' => $this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'InAppNotficationScreen',
            //     ]),
            //     'created_at' => now()->format('Y-m-d H:i:s'),
            //     'updated_at' => now()->format('Y-m-d H:i:s'),
            // ]);
            // }
            if (!DB::table('notifications')->where('notifiable_id', $this->record->user_id)->where('custom_json_data->complaint_id', $this->record?->id)->exists()) {
                $data = [];
                $data['notifiable_type'] = 'App\Models\User\User';
                $data['notifiable_id'] = $this->record->user_id;
                $data['url'] = $this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'InAppNotficationScreen';
                $data['title'] = ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' status';
                $data['body'] = ('Your ' . $this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' has been resolved by : ' . auth()->user()->first_name;
                $data['building_id'] = $this->record->building_id;
                $data['custom_json_data'] = json_encode([
                    'building_id' => $this->record->building_id,
                    'complaint_id' => $this->record?->id,
                    'user_id' => auth()->user()->id ?? null,
                    'owner_association_id' => $this->record?->owner_association_id,
                    'type' => 'Complaint',
                    'priority' => 'Medium',
                ]);
                NotificationTable($data);
            }

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
            $complaintType = 'Complaint';
            $remarks = Remark::where('complaint_id', $this->record->id)->get();

            ComplaintStatusMail::dispatch($this->record->user->email, $this->record->user->first_name, $remarks, $complaintType, $mailCredentials);

            if ($this->record->technician_id) {
                $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->technician_id)->pluck('token');
                if ($expoPushTokens->count() > 0) {
                    foreach ($expoPushTokens as $expoPushToken) {
                        $message = [
                            'to' => $expoPushToken,
                            'sound' => 'default',
                            'title' => ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' status',
                            'body' => 'A ' . ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
                            'data' => [
                                'notificationType' => 'ResolvedRequests',
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
                            ],
                        ];
                        $this->expoNotification($message);
                    }
                }
                // DB::table('notifications')->insert([
                //     'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                //     'type' => 'Filament\Notifications\DatabaseNotification',
                //     'notifiable_type' => 'App\Models\User\User',
                //     'notifiable_id' => $this->record->technician_id,
                //     'data' => json_encode([
                //         'actions' => [],
                //         'body' => 'A '.($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' has been resolved by '.$role->name.' : '.auth()->user()->first_name,
                //         'duration' => 'persistent',
                //         'icon' => 'heroicon-o-document-text',
                //         'iconColor' => 'warning',
                //         'title' => ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint').' status',
                //         'view' => 'notifications::notification',
                //         'viewData' => ['building_id' => $this->record->building_id,
                //                     'flat_id' => $this->record->flat_id],
                //         'format' => 'filament',
                //         'url' => 'InAppNotficationScreen',
                //         'building_id' => $this->record->flatVisitor->building_id,
                //         'flat_id' => $this->record->flatVisitor->flat_id
                //     ]),
                //     'created_at' => now()->format('Y-m-d H:i:s'),
                //     'updated_at' => now()->format('Y-m-d H:i:s'),
                // ]);
                if (!DB::table('notifications')->where('notifiable_id', $this->record->technician_id)->where('custom_json_data->complaint_id', $this->record?->id)->exists()) {
                    $data = [];
                    $data['notifiable_type'] = 'App\Models\User\User';
                    $data['notifiable_id'] = $this->record->technician_id;
                    $data['url'] = 'InAppNotficationScreen';
                    $data['title'] = ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' status';
                    $data['body'] = 'A ' . ($this->record->complaint_type === 'preventive_maintenance' ? 'PreventiveMaintenance' : 'complaint') . ' has been resolved by : ' . auth()->user()->first_name;
                    $data['building_id'] = $this->record?->building_id;
                    $data['flat_id'] = $this->record->flat_id ?? null;
                    $data['custom_json_data'] = json_encode([
                        'building_id' => $this->record->building_id,
                        'complaint_id' => $this->record?->id,
                        'user_id' => auth()->user()->id ?? null,
                        'owner_association_id' => $this->record?->owner_association_id,
                        'type' => 'Complaint',
                        'priority' => 'Medium',
                    ]);
                    NotificationTable($data);
                }
            }
        }

        if ($this->record->status == 'in-progress') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Complaint status',
                        'body' => 'Your complaint is moved to In-Progress',
                        'data' => [
                            'notificationType' => 'InAppNotficationScreen',
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id
                        ],
                    ];
                    $this->expoNotification($message);
                }
            }

            // DB::table('notifications')->insert([
            //     'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            //     'type' => 'Filament\Notifications\DatabaseNotification',
            //     'notifiable_type' => 'App\Models\User\User',
            //     'notifiable_id' => $this->record->user_id,
            //     'data' => json_encode([
            //         'actions' => [],
            //         'body' => 'Your complaint is moved to In-Progress',
            //         'duration' => 'persistent',
            //         'icon' => 'heroicon-o-document-text',
            //         'iconColor' => 'warning',
            //         'title' => 'Complaint status',
            //         'view' => 'notifications::notification',
            //         'viewData' => ['building_id' => $this->record->building_id,
            //                         'flat_id' => $this->record->flat_id],
            //         'format' => 'filament',
            //         'url' => 'InAppNotficationScreen',
            //     ]),
            //     'created_at' => now()->format('Y-m-d H:i:s'),
            //     'updated_at' => now()->format('Y-m-d H:i:s'),
            // ]);
            if (!DB::table('notifications')->where('notifiable_id', $this->record->user_id)->where('custom_json_data->complaint_id', $this->record?->id)->exists()) {
                $data = [];
                $data['notifiable_type'] = 'App\Models\User\User';
                $data['notifiable_id'] = $this->record->user_id;
                $data['url'] = 'InAppNotficationScreen';
                $data['title'] = 'Complaint status';
                $data['body'] = 'Your complaint is moved to In-Progress';
                $data['building_id'] = $this->record->building_id;
                $data['custom_json_data'] = json_encode([
                    'building_id' => $this->record->building_id,
                    'complaint_id' => $this->record?->id,
                    'user_id' => auth()->user()->id ?? null,
                    'owner_association_id' => $this->record?->owner_association_id,
                    'type' => 'Complaint',
                    'priority' => 'Medium',
                ]);
                NotificationTable($data);
            }
        }
    }
}
