<?php

namespace App\Filament\Resources\OaUserRegistrationResource\Pages;

use App\Filament\Resources\OaUserRegistrationResource;
use App\Jobs\AccountCreationJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditOaUserRegistration extends EditRecord
{
    protected static string $resource = OaUserRegistrationResource::class;
    protected ?string $heading        = 'Owner Association';

    public $value;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];

    }
    public function beforeSave()
    {
        $email_value = OwnerAssociation::where('id', $this->data['id'])->get();
        $this->value = $email_value->first()->email;
    }
    public function afterSave()
    {

        OwnerAssociation::where('id', $this->data['id'])
            ->update([
                'name'    => $this->record->name,
                'phone'   => $this->record->phone,
                'address' => $this->record->address,
            ]);
        User::where('owner_association_id', $this->data['id'])
            ->update([
                'first_name' => $this->record->name,
                'phone'      => $this->record->phone,
            ]);

        if ($this->record->verified == 'true') {
            OwnerAssociation::where('id', $this->data['id'])
                ->update([
                    'verified_by' => auth()->id(),

                ]);
            $password = Str::random(12);
            $user     = User::firstorcreate([
                'first_name'           => $this->record->name,
                'email'                => $this->record->email,
                'phone'                => $this->record->phone,
                'role_id'              => Role::where('name', 'OA')->value('id'),
                'password'             => Hash::make($password),
                'active'               => true,
                'owner_association_id' => $this->record->id,

            ]);
            AccountCreationJob::dispatch($user, $password);
        } else {

            OwnerAssociation::where('id', $this->data['id'])
                ->update([
                    'verified_by' => null,

                ]);
        }

    }
}
