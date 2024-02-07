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
    protected function beforeSave(): void
    {
        $user = User::find($this->record->user_id);
        if ($this->data['status'] == 'approved' && $this->record->status == null) {

            $password = Str::random(12);
            $user->password = Hash::make($password);
            $user->email_verified = true;
            $user->phone_verified = true;
            $user->active = true;
            $user->save();
            Residentapproval::dispatch($user, $password);
            Notification::make()
                ->title("Resident Approved")
                ->success()
                ->body("Resident approved successfully and passwoed sent to mail")
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
