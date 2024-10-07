<?php

namespace App\Filament\Resources\PropertyManagerResource\Pages;

use App\Filament\Resources\PropertyManagerResource;
use App\Jobs\PropertyManagerAccountCreationJob;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

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
        $data['active']      = true;

        return $data;
    }

    public static function beforeSave(Model $record): void
    {
        // Check if the phone number is already used by another user or owner association
        $phoneExists = DB::table('owner_associations')
            ->where('phone', $record->phone)
            ->where('id', '!=', $record->id)
            ->exists() ||
        DB::table('users')
            ->where('phone', $record->phone)
            ->exists();

        if ($phoneExists) {
            throw ValidationException::withMessages(['phone' => 'The phone number is already taken.']);
        }

        // Check if the email is already used by another user or owner association
        $emailExists = DB::table('owner_associations')
            ->where('email', $record->email)
            ->where('id', '!=', $record->id)
            ->exists() ||
        DB::table('users')
            ->where('email', $record->email)
            ->exists();

        if ($emailExists) {
            throw ValidationException::withMessages(['email' => 'The email address is already taken.']);
        }
    }

    protected function afterCreate()
    {
        $pmId = $this->record->id;

        $user = User::find($this->record->id);

        $roles = [
            // ['name' => 'Owner', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Vendor', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Managing Director', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Financial Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Building Engineer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Operations Engineer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Operations Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Staff', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Admin', 'owner_association_id' => $pmId,'guard_name' => 'web'],
            // ['name' => 'Tenant', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Security', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Technician', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Accounts Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'MD', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Complaint Officer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            // ['name' => 'Legal Officer', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Facility Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],
            ['name' => 'Property Manager', 'owner_association_id' => $pmId, 'guard_name' => 'web'],

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

            $permissionsConfig = config('pm-role-permission');

            Log::info('pm_id', [$this->record->id]);

            foreach ($permissionsConfig['roles'] as $roleName => $roleConfig) {
                $role = Role::where('name', $roleName)
                    ->where('owner_association_id', $this->record->id)->first();
                Log::info('Role' . $role);

                if (isset($roleConfig['permissions'])) {
                    $role->syncPermissions($roleConfig['permissions']);
                }
            }

        }

        PropertyManagerAccountCreationJob::dispatch($user, $password);

        $facilityManagerRole = Role::where('name', 'Facility Manager')
            ->where('owner_association_id', $pmId)
            ->first();

        if ($facilityManagerRole) {
            $allPermissions = Permission::all();
            $facilityManagerRole->syncPermissions($allPermissions);
        }

    }
}
