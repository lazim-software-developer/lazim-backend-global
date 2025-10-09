<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Models\Media;
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
use Illuminate\Support\Facades\Log;
use App\Models\ExpoPushNotification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ComplaintscomplaintResource;

class EditComplaintscomplaint extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ComplaintscomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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
        // $media = $this->record->media()->get()->map(function ($media) {
        //     return [
        //         'url' => $media->file_path,
        //         'id' => $media->id,
        //     ];
        // })->toArray();

      //  $data['media'] = $media;

        return $data;
    }

    public function beforeSave()
    {
        // $data = $this->form->getState();
        // dd($this->data,$this->form,$data);

        // 1️⃣ Find or create the remark for this complaint
        // $remark = $this->record->remarks()->latest()->first();

        // if ($remark) {
        //     $remark->update([
        //         'remarks' => $data['main_remarks'] ?? $remark->remarks,
        //         'status'  => $data['status'] ?? $remark->status,
        //     ]);
        // } else {
        //     $remark = Remark::create([
        //         'remarks'      => $data['main_remarks'],
        //         'type'         => 'Complaint',
        //         'status'       => $data['status'] ?? 'open',
        //         'user_id'      => auth()->user()->id,
        //         'complaint_id' => $this->record->id,
        //     ]);
        // }

        // 2️⃣ Attach uploaded files (Filament already uploaded them to S3)
        // if (!empty($data['remark_media'])) {
        //     foreach ($data['remark_media'] as $filePath) {
        //         // $filePath is already a string path like "remarks/abc.pdf"
        //         $remark->media()->create([
        //             'url'  => $filePath,
        //             'name' => 'before',
        //         ]);
        //     }
        // }

        DB::transaction(function () {
            $data = $this->form->getState();

            if (!empty($data['remarks'])) {
                // Get the latest remark by sorting keys (record-{id}) by ID
                $remarkData = collect($data['remarks'])->sortByDesc(function ($item, $key) {
                    return (int) str_replace('record-', '', $key);
                })->first();

                $remark = $this->record->remarks()->find($remarkData['id'] ?? null);

                if ($remark) {
                    // Update existing remark
                    $remark->update([
                        'remarks' => $remarkData['remarks'] ?? $remark->remarks,
                        'status' => $data['status'] ?? $remark->status,
                    ]);
                } else {
                    // Create new remark
                    $remark = $this->record->remarks()->create([
                        'remarks' => $remarkData['remarks'] ?? '',
                        'type' => 'Complaint',
                        'status' => $data['status'] ?? 'open',
                        'user_id' => auth()->user()->id,
                        'complaint_id' => $this->record->id,
                    ]);
                }

                // 2️⃣ Process media for the remark
                if (!empty($remarkData['media'])) {
                    foreach ($remarkData['media'] as $mediaKey => $media) {
                        // Skip existing media (record-{id} with id)
                        if (str_starts_with($mediaKey, 'record-') && isset($media['id'])) {
                            continue;
                        }
                        // Create new media record
                        $remark->media()->create([
                            'url' => $media['url'],
                            'name' => 'before', // Adjust as needed
                            'mediaable_id' => $remark->id,
                            'mediaable_type' => Remark::class,
                        ]);
                    }
                }
            } else {
                // No remarks provided, create a default remark
                $remark = $this->record->remarks()->create([
                    'remarks' => '',
                    'type' => 'Complaint',
                    'status' => $data['status'] ?? 'open',
                    'user_id' => auth()->user()->id,
                    'complaint_id' => $this->record->id,
                ]);
                Log::warning('No remarks provided, created default remark', [
                    'complaint_id' => $this->record->id,
                    'remark_id' => $remark->id,
                ]);
            }

            Log::info('Saved remark for complaint', [
                'complaint_id' => $this->record->id,
                'user_id' => auth()->user()->id,
                'remark_id' => $remark->id ?? null,
            ]);
        });
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
                        'body' => 'Your complaint has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
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
                    'body' => 'Your complaint has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
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
                            'title' => 'Complaint status',
                            'body' => 'A complain has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
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
                        'body' => 'A complain has been resolved by ' . $role->name . ' : ' . auth()->user()->first_name,
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

        if ($this->record->status == 'in-progress') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Complaint status',
                        'body' => 'Your complaint is moved to In-Progress',
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
                    'body' => 'Your complaint is moved to In-Progress',
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
