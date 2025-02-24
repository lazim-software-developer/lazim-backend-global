<?php

namespace App\Filament\Resources\BuildingEngineerResource\Pages;

use App\Filament\Resources\BuildingEngineerResource;
use App\Jobs\AccountsManagerJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateBuildingEngineer extends CreateRecord
{
    protected static string $resource = BuildingEngineerResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['phone']                = '971' . $data['phone'];
        $data['email_verified']       = true;
        $data['phone_verified']       = true;
        $data['role_id']              = Role::where('name', 'Building Engineer')->first()->id;
        $data['owner_association_id'] = auth()->user()?->owner_association_id;

        return $data;
    }
    protected function afterCreate(): void
    {
        $user           = $this->record;
        $password       = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();

        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
        // $emailCredentials = OwnerAssociation::findOrFail($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

        AccountsManagerJob::dispatch($user, $password,);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
