<?php

namespace App\Filament\Resources\MDResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\MDResource;
use App\Jobs\MdCreateJob;
use Filament\Resources\Pages\CreateRecord;

class CreateMD extends CreateRecord
{
    protected static string $resource = MDResource::class;
    protected static ?string $title = 'MD';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        
        $data['phone'] = '971'.$data['phone'];
        $data['email_verified'] = true;
        $data['phone_verified'] = true;
        $data['role_id'] = Role::where('name', 'MD')->first()->id;
        $data['owner_association_id'] = auth()->user()->owner_association_id;

        return $data;
    }
    protected function afterCreate(): void
    {
        $user = $this->record;
        $password = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();
        MdCreateJob::dispatch($user, $password);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
