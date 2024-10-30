<?php

namespace App\Filament\Resources\TechnicianVendorResource\Pages;

use App\Filament\Resources\TechnicianVendorResource;
use App\Jobs\TechnicianAccountCreationJob;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use DB;
use Exception;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Log;
use Vinkla\Hashids\Facades\Hashids;

class CreateTechnicianVendor extends CreateRecord
{
    protected static string $resource = TechnicianVendorResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        DB::beginTransaction();

        try {
            $oaId = auth()->user()->owner_association_id;
            $role = $this->getOrCreateTechnicianRole($oaId);

            $plainPassword = Str::random(12);

            $userData = [
                'first_name'           => $data['first_name'],
                'last_name'            => $data['last_name'] ?? null,
                'email'                => $data['email'],
                'phone'                => $data['phone'] ?? null,
                'email_verified'       => true,
                'phone_verified'       => true,
                'active'               => true,
                'role_id'              => $role->id,
                'owner_association_id' => $oaId,
                'password'             => Hash::make($plainPassword),
            ];

            $user = User::create($userData);
            Log::info('Record ==>',[$user]);

            $technicianData = [
                'technician_id'     => $user->id,
                'vendor_id'            => $data['vendor_id'],
                'technician_number' => null,
                'owner_association_id' => $oaId,
            ];

            $technician = parent::handleRecordCreation($technicianData);

            $serviceTechnicianVendorData = [
                'technician_vendor_id' => $technician->id,
                'service_id' => $data['service_id'][0],
                'owner_association_id' => $oaId,
            ];
            // dd($serviceTechnicianVendorData);

            DB::table('service_technician_vendor')->insert($serviceTechnicianVendorData);

            DB::commit();

            TechnicianAccountCreationJob::dispatch($user, $plainPassword);

            return $technician;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating technician:', [
                'error'                => $e->getMessage(),
                'trace'                => $e->getTraceAsString(),
                'data'                 => $data,
                'owner_association_id' => $oaId ?? null,
            ]);

            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        $technician                       = $this->record;
        Log::info('Record ==>',[$technician]);
        $technician->active               = true;
        $technician->owner_association_id = auth()->user()->owner_association_id;

        $vendorId = $this->record->vendor_id;
        $name     = Vendor::where('id', $vendorId)->first()->name;
        $userId   = User::where('id', $technician->id)->first()->id;

        $technician->technician_number = strtoupper(substr($name, 0, 2)) .
        Hashids::connection('alternative')->encode($userId);

        $technician->save();
    }

    protected function getOrCreateTechnicianRole($ownerAssociationId)
    {
        return DB::transaction(function () use ($ownerAssociationId) {
            $existingRole = Role::where('name', 'Technician')
                ->where('owner_association_id', $ownerAssociationId)
                ->first();

            if ($existingRole) {
                return $existingRole;
            }

            $tempName = 'Technician_' . $ownerAssociationId . '_' . Str::random(8);

            $newRole = Role::create([
                'name'                 => $tempName,
                'owner_association_id' => $ownerAssociationId,
                'guard_name'           => 'web',
                'is_active'            => true,
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $newRole->update(['name' => 'Technician']);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return $newRole->fresh();
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
