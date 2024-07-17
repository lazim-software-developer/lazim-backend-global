<?php

namespace App\Filament\Resources\LegalOfficerResource\Pages;

use App\Filament\Resources\LegalOfficerResource;
use App\Jobs\AccountsManagerJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateLegalOfficer extends CreateRecord
{
    protected static string $resource = LegalOfficerResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['phone']                = '971' . $data['phone'];
        $data['email_verified']       = true;
        $data['phone_verified']       = true;
        $data['role_id']              = Role::where('name', 'Legal Officer')->first()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;

        return $data;
    }
    protected function afterCreate(): void
    {
        $user           = $this->record;
        $password       = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
        $emailCredentials = OwnerAssociation::findOrFail($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

        AccountsManagerJob::dispatch($user, $password, $emailCredentials);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
