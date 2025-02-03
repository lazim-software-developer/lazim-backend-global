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
        $vendor        = $this->record;
        $approvalStatus = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->first();
        $user          = $vendor->user;
        $manager       = $vendor->managers->first();
        $services      = $vendor->services->pluck('id')->toArray();
        $subcategories = $vendor->services->pluck('subcategory_id')->unique()->toArray();
        $riskPolicy    = $vendor->documents()->where('name', 'risk_policy')->first();

        return [
            'owner_association_id' => $vendor->owner_association_id,
            'name'                 => $vendor->name,
            'user'                 => [
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
            ],
            'address_line_1'       => $vendor->address_line_1 ?? '',
            'landline_number'      => $vendor->landline_number ?? '',
            'website'              => $vendor->website ?? '',
            'tl_number'            => $vendor->tl_number ?? '',
            'tl_expiry'            => $vendor->tl_expiry,
            'risk_policy_expiry'   => $riskPolicy ? $riskPolicy->expiry_date : null,
            'status'               => $approvalStatus?->status ?? 'pending',
            'remarks'              => $approvalStatus?->remarks ?? null,
            'subcategory_id'       => $subcategories,
            'service_id'           => $services,
            'managers'             => [[
                'name'  => $manager->name ?? '',
                'email' => $manager->email ?? '',
                'phone' => $manager->phone ?? '',
            ]],
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update([
                'name'                 => $data['name'],
                'owner_association_id' => auth()->user()->owner_association_id,
                'address_line_1'       => $data['address_line_1'],
                'landline_number'      => $data['landline_number'] ?? null,
                'website'              => $data['website'] ?? null,
                'status'               => $record?->status == null ? $data['status'] : $record?->status,
                'remarks'              => $record?->remarks == null ? $data['remarks'] : $record?->remarks,
                'tl_number'            => $data['tl_number'],
                'tl_expiry'            => $data['tl_expiry'],
            ]);

            if ($data['status'] == 'rejected') {
                DB::table('owner_association_vendor')
                    ->where('vendor_id', $record->id)
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->update(['status' => $data['status'], 'remarks' => $data['remarks']]);
            }
            if ($data['status'] == 'approved') {
                DB::table('owner_association_vendor')
                    ->where('vendor_id', $record->id)
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->update(['status' => $data['status'], 'active' => true]);
            }

            if ($record->user) {
                $record->user->update([
                    'first_name'           => $data['name'],
                    'owner_association_id' => auth()->user()->owner_association_id,
                ]);
            }

            if (isset($data['risk_policy_expiry'])) {
                Document::updateOrCreate(
                    [
                        'documentable_id'   => $record->id,
                        'documentable_type' => Vendor::class,
                        'name'              => 'risk_policy',
                    ],
                    [
                        'document_library_id'  => DocumentLibrary::where('name', 'Risk policy')->first()->id,
                        'owner_association_id' => auth()->user()->owner_association_id,
                        'status'               => 'pending',
                        'expiry_date'          => $data['risk_policy_expiry'],
                    ]
                );
            }

            if (!empty($data['service_id'])) {
                $record->services()->sync($data['service_id']);
            }

            if (!empty($data['managers'][0]['name']) && !empty($data['managers'][0]['email'])) {
                $record->managers()->updateOrCreate(
                    [],
                    [
                        'name'  => $data['managers'][0]['name'],
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
