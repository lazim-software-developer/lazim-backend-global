<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Filament\Resources\FacilityManagerResource;
use App\Jobs\FacilityManagerJob;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorManager;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class CreateFacilityManager extends CreateRecord
{
    protected static string $resource = FacilityManagerResource::class;

    public function afterCreate()
    {
        $user = $this->record;
        if (!$user) {
            return;
        }

        $password       = Str::random(12);
        $hashedPassword = Hash::make($password);
        $user->password = $hashedPassword;
        $user->save();

        FacilityManagerJob::dispatch($user, $password);

        $role = Role::where('name', 'Facility Manager')->first();

        if ($role) {
            $permissions = Permission::all();

            $role->syncPermissions($permissions);

            $user->assignRole($role);
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        // $password = Str::random(12);

        try {
            return DB::transaction(function () use ($data) {
                // Create User (Vendor Registration)
                $user = User::create([
                    'owner_association_id' => $data['oa_id'],
                    'first_name'           => $data['company_name'],
                    'email'                => $data['email'],
                    'phone'                => $data['phone'],
                    // 'password'             => Hash::make($password),
                    'email_verified'       => true,
                    'phone_verified'       => true,
                    'active'               => true,
                    'role_id'              => Role::where('name', 'Facility Manager')->value('id'),
                ]);

                // Create Vendor (Company Details)
                $vendor = Vendor::create([
                    'user_id'              => $user->id,
                    'address_line_1'       => $data['address'],
                    'name'                 => $data['company_name'],
                    'owner_id'             => $user->id,
                    'landline_number'      => $data['landline'],
                    'website'              => $data['website'],
                    'fax'                  => $data['fax'],
                    'tl_number'            => $data['tl_number'],
                    'tl_expiry'            => $data['trade_license_expiry'],
                    'owner_association_id' => $data['oa_id'],
                    'risk_policy_expiry'   => $data['risk_policy_expiry'],
                ]);

                // Create VendorManager (Manager Details) - Optional
                if (!empty($data['manager_name']) && !empty($data['manager_email'])) {
                    VendorManager::create([
                        'vendor_id' => $vendor->id,
                        'name'      => $data['manager_name'],
                        'email'     => $data['manager_email'],
                        'phone'     => $data['manager_phone'],
                    ]);
                }

                return $user;
            });
        } catch (QueryException $e) {
            Log::error('Error creating records: ' . $e->getMessage());
            Log::error('SQL: ' . $e->getSql());
            Log::error('Bindings: ' . json_encode($e->getBindings()));
            throw new Halt($e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
