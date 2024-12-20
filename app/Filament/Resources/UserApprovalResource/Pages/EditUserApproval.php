<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use App\Models\UserApproval;
use App\Jobs\Residentapproval;
use Filament\Facades\Filament;
use App\Jobs\ResidentRejection;
use App\Jobs\AccountsManagerJob;
use App\Models\OwnerAssociation;
use App\Models\UserApprovalAudit;
use App\Models\AccountCredentials;
use App\Models\Building\FlatTenant;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\UserApprovalResource;

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
    protected function beforeSave(): void
    {
        UserApproval::find($this->data['id'])->update([
            'updated_by'  => auth()->user()->id,
        ]);
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;

        // if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
        //     $emailCredentials = OwnerAssociation::find($this->record->owner_association_id)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        // }else{
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        // }
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];

        $user = User::find($this->record->user_id);
        $pm_oa = auth()->user()?->first_name ?? '';
        if ($this->data['status'] == 'approved' && $this->record->status == null) {
            $user->active = true;
            $user->save();
            FlatTenant::where(['tenant_id'=>$user->id,'flat_id'=>$this->record->flat_id,'active'=>false])->latest()->first()?->update(['active'=>true]);
            Residentapproval::dispatch($user, $mailCredentials,$pm_oa);
            Notification::make()
                ->title("Resident Approved")
                ->success()
                ->body("Resident approved successfully")
                ->send();
        }
        if ($this->data['status'] == 'rejected' && $this->record->status == null) {
            ResidentRejection::dispatch($user, $mailCredentials,$this->record,$pm_oa);
            Notification::make()
                ->title("Resident Rejected")
                ->danger()
                ->body("Resident has been rejected")
                ->send();
        }
        if($this->record->status == null){
            UserApprovalAudit::where('user_approval_id', $this->record->id)->where('status', null)->first()?->update([
                'status' => $this->data['status'],
                'remarks' => $this->data['remarks'],
                'updated_by' => auth()->user()->id,
            ]);
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
    return null;
    }
}
