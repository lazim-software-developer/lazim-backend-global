<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Filament\Resources\FacilityManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditFacilityManager extends EditRecord
{
    protected static string $resource = FacilityManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user          = $this->record;
        $vendor        = $user->vendors()->first();
        $vendorManager = $vendor ? $vendor->managers()->first() : null;

        $data = array_merge($data, [
            'oa_id'        => $user->owner_association_id,
            'company_name' => $user->first_name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'active'       => $user->active,
        ]);

        if ($vendor) {
            $data = array_merge($data, [
                'name'                 => $vendor->name ?? '',
                'address'              => $vendor->address_line_1 ?? '',
                'landline'             => $vendor->landline_number ?? '',
                'website'              => $vendor->website ?? '',
                'fax'                  => $vendor->fax ?? '',
                'tl_number'            => $vendor->tl_number ?? '',
                'trade_license_expiry' => $vendor->tl_expiry,
                'risk_policy_expiry'   => $vendor->risk_policy_expiry,
            ]);
        }

        if ($vendorManager) {
            $data = array_merge($data, [
                'manager_name'  => $vendorManager->name ?? '',
                'manager_email' => $vendorManager->email ?? '',
                'manager_phone' => $vendorManager->phone ?? '',
            ]);
        }

        // Ensure all form fields have a value, even if it's an empty string
        $formFields = [
            'oa_id', 'company_name', 'email', 'phone', 'active',
            'name', 'address', 'landline', 'website', 'fax', 'tl_number',
            'trade_license_expiry', 'risk_policy_expiry',
            'manager_name', 'manager_email', 'manager_phone',
        ];

        foreach ($formFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = '';
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Update User
            $userUpdateData = array_filter([
                'owner_association_id' => $data['oa_id'] ?? null,
                'first_name'           => $data['company_name'] ?? null,
                'email'                => $data['email'] ?? null,
                'phone'                => $data['phone'] ?? null,
                'active'               => $data['active'] ?? null,
            ]);

            $record->update($userUpdateData);

            // Update or Create Vendor
            $vendorUpdateData = array_filter([
                'address_line_1'       => $data['address'] ?? null,
                'name'                 => $data['name'] ?? null,
                'landline_number'      => $data['landline'] ?? null,
                'website'              => $data['website'] ?? null,
                'fax'                  => $data['fax'] ?? null,
                'tl_number'            => $data['tl_number'] ?? null,
                'tl_expiry'            => $data['trade_license_expiry'] ?? null,
                'owner_association_id' => $data['oa_id'] ?? null,
                'risk_policy_expiry'   => $data['risk_policy_expiry'] ?? null,
            ]);

            $vendor = $record->vendors()->updateOrCreate(
                ['owner_id' => $record->id],
                $vendorUpdateData
            );

            // Update or Create VendorManager
            if (!empty($data['manager_name']) && !empty($data['manager_email'])) {
                $managerUpdateData = [
                    'name'  => $data['manager_name'],
                    'email' => $data['manager_email'],
                    'phone' => $data['manager_phone'] ?? null,
                ];

                $vendor->managers()->updateOrCreate([], $managerUpdateData);
            } else {
                // If manager details are empty, delete all existing managers for this vendor
                $vendor->managers()->delete();
            }

            return $record;
        });
    }
}
