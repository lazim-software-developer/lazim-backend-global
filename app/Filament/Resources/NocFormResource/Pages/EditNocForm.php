<?php

namespace App\Filament\Resources\NocFormResource\Pages;

use App\Filament\Resources\NocFormResource;
use App\Jobs\SaleNocMailJob;
use App\Models\ExpoPushNotification;
use App\Models\OwnerAssociation;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditNocForm extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = NocFormResource::class;
    protected static ?string $title = 'Sale NOC';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function beforeSave(){
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
        $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

        if($this->record->admin_document != $this->data['admin_document']){
            $user= $this->record->user;
            $file = $this->data['admin_document'];
            SaleNocMailJob::dispatch($user,$file,$emailCredentials);
        }
    }
    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Sales NOC form status',
                        'body' => 'Your sales noc form has been approved.',
                        'data' => ['notificationType' => 'MyRequest'],
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
                            'body' => 'Your sales noc form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Sales NOC form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }

        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Sales NOC form status',
                        'body' => 'Your sales noc form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest'],
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
                            'body' => 'Your sales noc form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Sales NOC form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }
    }
}
