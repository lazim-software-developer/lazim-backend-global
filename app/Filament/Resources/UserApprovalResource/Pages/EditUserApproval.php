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
use App\Models\UserApproval;

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
        UserApproval::find($this->data['id'])->update([
            'updated_by'  => auth()->user()->id,
        ]);
        $user = User::find($this->record->user_id);
        if ($this->data['status'] == 'approved' && $this->record->status == null) {
            $user->active = true;
            $user->save();
            Residentapproval::dispatch($user);
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
