<?php

namespace App\Filament\Resources\OaUserRegistrationResource\Pages;

use App\Filament\Resources\OaUserRegistrationResource;
use App\Jobs\AccountCreationJob;
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
        $email_value = OaUserRegistration::where('id', $this->data['id'])->get();
        $this->value = $email_value->first()->email;
        // $this->password = $email_value->first()->password;
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
        $oadetails = OaDetails::where('oa_id', $this->record->oa_id)->first();
        $password = Str::random(12);
        User::where('id', $oadetails->user_id)
            ->update([
                'first_name' => $this->record->name,
                'email'      => $this->record->email,
                'phone'      => $this->record->phone,
                'password'  => Hash::make($password),
            ]);

        $user     = User::where('id',$oadetails->user_id)->first();
        if ($this->value != $this->record->email) {
            AccountCreationJob::dispatch($user, $password);

        }

    }
}
