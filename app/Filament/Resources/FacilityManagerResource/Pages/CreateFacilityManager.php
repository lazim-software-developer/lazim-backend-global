<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Filament\Resources\FacilityManagerResource;
use App\Jobs\FacilityManagerJob;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
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

class CreateFacilityManager extends CreateRecord
{
    protected static string $resource = FacilityManagerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return DB::transaction(function () use ($data) {

                $password = Str::random(12);
                $userData = [
                    'first_name'           => $data['name'],
                    'email'                => $data['user']['email'],
                    'phone'                => $data['user']['phone'],
                    'password'             => Hash::make($password),
                    'email_verified'       => true,
                    'phone_verified'       => true,
                    'active'               => true,
                    'role_id'              => Role::where('name', 'Facility Manager')->value('id'),
                    'owner_association_id' => auth()->user()->owner_association_id,
                ];

                $user = User::create($userData);

                $vendorData = [
                    'name'                 => $data['name'],
                    'owner_id'             => $user->id,
                    'owner_association_id' => auth()->user()->owner_association_id,
                    'address_line_1'       => $data['address_line_1'],
                    'landline_number'      => $data['landline_number'] ?? null,
                    'website'              => $data['website'] ?? null,
                    'fax'                  => null,
                    'tl_number'            => $data['tl_number'],
                    'tl_expiry'            => $data['tl_expiry'],
                ];

                $vendor = Vendor::create($vendorData);

                if (isset($data['risk_policy_expiry'])) {
                    try {
                        $documentData = [
                            'name'                 => 'risk_policy',
                            'document_library_id'  => DocumentLibrary::where('name', 'Risk policy')->first()->id,
                            'owner_association_id' => auth()->user()->owner_association_id,
                            'status'               => 'pending',
                            'documentable_id'      => $vendor->id,
                            'documentable_type'    => Vendor::class,
                            'expiry_date'          => $data['risk_policy_expiry'],
                        ];

                        Document::create($documentData);
                    } catch (\Exception $e) {
                        Log::error('Error creating document:', ['error' => $e->getMessage()]);
                    }
                }
                if (isset($data['tl_expiry'])) {
                    try {
                        $documentData = [
                            'name'                 => 'tl_document',
                            'document_library_id'  => DocumentLibrary::where('name', 'TL document')->first()->id,
                            'owner_association_id' => auth()->user()->owner_association_id,
                            'status'               => 'pending',
                            'documentable_id'      => $vendor->id,
                            'documentable_type'    => Vendor::class,
                            'expiry_date'          => $data['tl_expiry'],
                        ];

                        Document::create($documentData);
                    } catch (\Exception $e) {
                        Log::error('Error creating document:', ['error' => $e->getMessage()]);
                    }
                }

                $oa_vendorData = [
                    'owner_association_id' => auth()->user()->owner_association_id,
                    'vendor_id'            => $vendor->id,
                    'from'                 => $user->created_at,
                    'active'               => true,
                    'type'                 => 'Vendor',
                ];

                DB::table('owner_association_vendor')->insert($oa_vendorData);

                if (!empty($data['service_id'])) {
                    foreach ($data['service_id'] as $serviceId) {
                        $serviceData = [
                            'service_id'           => $serviceId,
                            'vendor_id'            => $vendor->id,
                            'price'                => null,
                            'start_date'           => null,
                            'end_date'             => null,
                            'active'               => true,
                            'building_id'          => null,
                            'contract_id'          => null,
                            'owner_association_id' => auth()->user()->owner_association_id,
                        ];

                        ServiceVendor::create($serviceData);
                    }
                }

                if (!empty($data['managers'][0]['name'] ?? null) && !empty($data['managers'][0]['email'] ?? null)) {
                    try {
                        $managerData = [
                            'vendor_id' => $vendor->id,
                            'name'      => $data['managers'][0]['name'],
                            'email'     => $data['managers'][0]['email'],
                            'phone'     => $data['managers'][0]['phone'] ?? null,
                        ];

                        VendorManager::create($managerData);
                    } catch (\Exception $e) {
                        Log::error('Error creating vendor manager:', ['error' => $e->getMessage()]);
                    }
                }

                try {
                    FacilityManagerJob::dispatch($user, $password);
                } catch (\Exception $e) {
                    Log::error('Error dispatching FacilityManagerJob:', ['error' => $e->getMessage()]);
                }

                return $vendor;
            });
        } catch (QueryException $e) {
            Log::error('Database error in handleRecordCreation:', [
                'message'  => $e->getMessage(),
                'sql'      => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            throw new Halt('Error creating facility manager: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in handleRecordCreation:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new Halt('Unexpected error: ' . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
