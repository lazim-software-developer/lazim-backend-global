<?php

namespace App\Filament\Resources\PropertyManagerResource\Pages;

use App\Filament\Resources\PropertyManagerResource;
use App\Jobs\PropertyManagerAccountCreationJob;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreatePropertyManager extends CreateRecord
{
    protected static string $resource = PropertyManagerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['verified_by'] = auth()->user()->id;
        $data['verified']    = 1;
        $data['role']        = 'Property Manager';
        return $data;
    }

    protected function afterCreate()
    {
        $pmId = $this->record->id;

        $roles = [
            ['name' => 'Owner', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Vendor', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Managing Director', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Financial Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Building Engineer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Operations Engineer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Operations Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Staff', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Admin', 'owner_association_id' => $pmId,'guard_name' => 'web'],
            ['name' => 'Property Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Tenant', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Security', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Technician', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Accounts Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'MD', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Complaint Officer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Legal Officer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Facility Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],

        ];

        DB::table('roles')->insert($roles);

        $emailexists = User::where(['email' => $this->record->email, 'phone' => $this->record->phone])->exists();
        if (!$emailexists) {
            $password = Str::random(12);

            $user = User::firstorcreate(
                [
                    'email' => $this->record->email,
                    'phone' => $this->record->phone,
                ],
                [
                    'first_name'           => $this->record->name,
                    'profile_photo'        => $this->record->profile_photo,
                    'role_id'              => Role::where('name', 'Property Manager')
                        ->where('owner_association_id', $pmId)->value('id'),
                    'active'               => $this->record->active,
                    'password'             => Hash::make($password),
                    'owner_association_id' => $this->record->id,
                    'email_verified'       => 1,
                    'phone_verified'       => 1,
                ]
            );
            $user->ownerAssociation()->attach($this->record->id, ['from' => now()->toDateString()]);

            $pm = Role::where('name', 'Property Manager')
                ->where('owner_association_id', $this->record->id)->first();

            DB::table('model_has_roles')->insert([
                'role_id'    => $pm->id,
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);

        }

        PropertyManagerAccountCreationJob::dispatch($user, $password);

    }
}
