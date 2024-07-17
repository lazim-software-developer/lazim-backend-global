<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Jobs\AccountsManagerJob;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\UserApprovalResource;
use App\Jobs\Residentapproval;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\UserApproval;
use Filament\Facades\Filament;

class EditUserApproval extends EditRecord
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::find($data['user_id']);
        $data['user'] = $user->first_name;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone;

        return $data;
    }
    protected function beforeSave($record): void
    {
        UserApproval::find($this->data['id'])->update([
            'updated_by'  => auth()->user()->id,
        ]);
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;

        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            $emailCredentials = OwnerAssociation::find($record->owner_association_id)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        }else{
            $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        }

        $user = User::find($this->record->user_id);
        if ($this->data['status'] == 'approved' && $this->record->status == null) {
            $user->active = true;
            $user->save();
            Residentapproval::dispatch($user,$emailCredentials);
            Notification::make()
                ->title("Resident Approved")
                ->success()
                ->body("Resident approved successfully")
                ->send();
        }
        if ($this->data['status'] == 'rejected' && $this->record->status == null) {
            Notification::make()
                ->title("Resident Rejected")
                ->danger()
                ->body("Resident has been rejacted")
                ->send();
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
