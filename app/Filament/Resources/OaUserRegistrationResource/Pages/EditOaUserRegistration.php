<?php

namespace App\Filament\Resources\OaUserRegistrationResource\Pages;

use App\Filament\Resources\OaUserRegistrationResource;
use App\Jobs\AccountCreationJob;
use App\Models\Master\Role;
use App\Models\OaDetails;
use App\Models\OaUserRegistration;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditOaUserRegistration extends EditRecord
{
    protected static string $resource = OaUserRegistrationResource::class;
    protected ?string $heading        = 'OA User';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];

    }

    public function afterSave()
    {

        if ($this->data['verified'] == 'true') {
            OaUserRegistration::where('id', $this->data['id'])
                ->update([
                    'verified_by' => auth()->id(),

                ]);
        } else {

            OaUserRegistration::where('id', $this->data['id'])
                ->update([
                    'verified_by' => null,

                ]);

        }
        if (!OaDetails::where('oa_id', $this->record->oa_id)->exists()) {
            $password = Str::random(12);
            $user     = User::firstorcreate([
                'first_name' => $this->record->name,
                'email'      => $this->record->email,
                'phone'      => $this->record->phone,
                'role_id'    => Role::where('name', 'OA')->value('id'),
                'password'   => Hash::make($password),
                'active'     => true]);
            AccountCreationJob::dispatch($user, $password);
            OaDetails::firstorcreate([
                'oa_id'   => $this->record->oa_id,
                'user_id' => User::where('email', $this->record->email)->value('id'),
            ]);
        } else {
            $oadetails = OaDetails::where('oa_id', $this->record->oa_id)->first();
            User::where('id', $oadetails->user_id)
                ->update([
                    'first_name' => $this->record->name,
                    'email'      => $this->record->email,
                    'phone'      => $this->record->phone,
                ]);

        }
    }

}
