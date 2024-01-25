<?php

namespace App\Filament\Resources\ComplaintOfficerResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use App\Jobs\AccountsManagerJob;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ComplaintOfficerResource;

class CreateComplaintOfficer extends CreateRecord
{
    protected static string $resource = ComplaintOfficerResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        
        $data['phone'] = '971'.$data['phone'];
        $data['email_verified'] = true;
        $data['phone_verified'] = true;
        $data['role_id'] = Role::where('name', 'Complaint Officer')->first()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;

        return $data;
    }
    protected function afterCreate(): void
    {
        $user = $this->record;
        $password = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();
        AccountsManagerJob::dispatch($user, $password);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
