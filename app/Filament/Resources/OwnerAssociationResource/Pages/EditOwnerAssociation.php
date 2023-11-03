<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use App\Filament\Resources\OwnerAssociationResource;
use App\Jobs\AccountCreationJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EditOwnerAssociation extends EditRecord
{
    protected static string $resource =OwnerAssociationResource::class;
    protected ?string $heading = 'Owner Association';

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
        // $phone = OwnerAssociation::where('id',$this->data['id'])->pluck('phone');
        // dd($phone->first() == $this->data['phone']);
        OwnerAssociation::where('id', $this->data['id'])
            ->update([
                'name'    => $this->record->name,
                'phone'   => $this->record->phone,
                'address' => $this->record->address,
                'active'  => $this->record->active,
            ]);
        User::where('owner_association_id', $this->data['id'])
            ->update([
                'first_name' => $this->record->name,
                'phone'      => $this->record->phone,
            ]);

        // If updated value of verified is true and the value is DB is false(This happens only for the first time)
        if($this->record->verified == 'true' && DB::table('owner_associations')->where('id',$this->record->id)->value('verified_by') == null) {
            // Update verified in owner_association table
            OwnerAssociation::where('id', $this->data['id'])
                ->update([
                    'verified_by' => auth()->id(),
                ]);

            // Create an entry in Users table
            // check if entered email and phone number is already present for other users in users table
            $emailexists = User::where(['email' => $this->record->email, 'phone' =>$this->record->phone])->exists();
            if(!$emailexists) {
                $password = Str::random(12);

                $user = User::firstorcreate([
                    'first_name'           => $this->record->name,
                    'email'                => $this->record->email,
                    'phone'                => $this->record->phone,
                    'role_id'              => Role::where('name', 'OA')->value('id'),
                    'active'               => true,
                    'password' => Hash::make($password),
                    'owner_association_id' => $this->record->id,
                ]);
                // Send email with credentials
                AccountCreationJob::dispatch($user, $password);
            } else {
                // No need to handle this - Subhash
            }
        }

        // if account is verified and other fields are updated

    }
}
