<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use App\Models\Building\Complaint;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

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
                        'title' => 'Help Desk complaint status',
                        'body' => 'A complaint has been resolved by a ' . $role->name . ' ' . auth()->user()->first_name,
                        'data' => ['notificationType' => 'HelpDeskTab'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'A complaint has been resolved by a ' . $role->name . ' ' . auth()->user()->first_name,
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Help Desk complaint status',
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
