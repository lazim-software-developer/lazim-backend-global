<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Filament\Resources\FacilityManagerResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Vendor\Vendor;
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
        $vendor = $this->record;
        $user = $vendor->user;
        $manager = $vendor->managers->first();
        $riskPolicy = $vendor ? $vendor->documents()->where('name', 'risk_policy')->first() : null;


        return [
            'owner_association_id' => $vendor->owner_association_id,
            'name' => $vendor->name,
            'user' => [
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
            ],
            'address_line_1' => $vendor->address_line_1 ?? '',
            'landline_number' => $vendor->landline_number ?? '',
            'website' => $vendor->website ?? '',
            'fax' => $vendor->fax ?? '',
            'tl_number' => $vendor->tl_number ?? '',
            'tl_expiry' => $vendor->tl_expiry,
            'risk_policy_expiry' => $riskPolicy->expiry_date ?? null,
            'managers' => [[
                'name' => $manager->name ?? '',
                'email' => $manager->email ?? '',
                'phone' => $manager->phone ?? '',
            ]],
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Update Vendor
            $record->update([
                'name' => $data['name'],
                'owner_association_id' => $data['owner_association_id'],
                'address_line_1' => $data['address_line_1'],
                'landline_number' => $data['landline_number'] ?? null,
                'website' => $data['website'] ?? null,
                'fax' => $data['fax'] ?? null,
                'tl_number' => $data['tl_number'],
                'tl_expiry' => $data['tl_expiry'],
            ]);

            // Update related User
            if ($record->user) {
                $record->user->update([
                    'first_name' => $data['name'],
                    'owner_association_id' => $data['owner_association_id'],
                ]);
            }

            // Update or Create Risk Policy Document
            if (isset($data['risk_policy_expiry'])) {
                Document::updateOrCreate(
                    [
                        'documentable_id' => $record->id,
                        'documentable_type' => Vendor::class,
                        'name' => 'risk_policy',
                    ],
                    [
                        'document_library_id' => DocumentLibrary::where('name', 'Risk policy')->first()->id,
                        'owner_association_id' => $data['owner_association_id'],
                        'status' => 'pending',
                        'expiry_date' => $data['risk_policy_expiry'],
                    ]
                );
            }

            // Update or Create VendorManager
            if (!empty($data['managers'][0]['name']) && !empty($data['managers'][0]['email'])) {
                $record->managers()->updateOrCreate(
                    [],
                    [
                        'name' => $data['managers'][0]['name'],
                        'email' => $data['managers'][0]['email'],
                        'phone' => $data['managers'][0]['phone'] ?? null,
                    ]
                );
            } else {
                $record->managers()->delete();
            }

            return $record;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
