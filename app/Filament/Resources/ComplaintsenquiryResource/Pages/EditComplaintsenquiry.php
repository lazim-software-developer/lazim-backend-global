<?php

namespace App\Filament\Resources\ComplaintsenquiryResource\Pages;

use App\Filament\Resources\ComplaintsenquiryResource;
use App\Jobs\ComplaintStatusMail;
use App\Models\AccountCredentials;
use App\Models\ExpoPushNotification;
use App\Models\Remark;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditComplaintsenquiry extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = ComplaintsenquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function beforeSave()
    {
        $data = $this->form->getState();
        
        if ((array_key_exists('remarks', $data) && $data['remarks'] != $this->record->remarks) || (array_key_exists('status', $data) && $data['status'] != $this->record->status)){

            Remark::create([
                'remarks' => $data['remarks'],
                'type' => 'Enquiry',
                'status' => $data['status'],
                'user_id' => auth()->user()->id,
                'complaint_id' => $this->record->id,
            ]);
        }

    }

    public function afterSave()
    {
        if ($this->record->status == 'closed') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Enquiry Acknowledgement',
                        'body' => 'Your enquiry has been acknowledged by '.auth()->user()->first_name. '. Team will contact you soon.',
                        'data' => ['notificationType' => 'InAppNotficationScreen'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your enquiry has been acknowledged by '.auth()->user()->first_name. '. Team will contact you soon.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Enquiry Acknowledgement',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
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
                    $complaintType = "Enquiry";
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
                        'title' => 'Enquiry Status',
                        'body' => 'Your enquiry is moved to In-Progress',
                        'data' => ['notificationType' => 'InAppNotficationScreen'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your enquiry is moved to In-Progress',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Enquiry Status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => '',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}
