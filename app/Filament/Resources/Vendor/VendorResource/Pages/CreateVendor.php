<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use App\Filament\Resources\Vendor\VendorResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;
    // protected function afterCreate()
    // {
    //     $service = Vendor::where('id', $this->record->id)->first()->service;
    //     if ($service == 'other') {
    //         $other = Vendor::where('id', $this->record->id)->first()->other;
    //         Service::create([
    //             'name'   => $other,
    //             'custom' => 1,
    //             'active' => 1,
    //         ]);

    //     }
    //     $id          = $this->record->id;
    //     $user        = $this->record->owner_id;
    //     $library_id  = DocumentLibrary::where('name', 'tl_document')->first()->id;
    //     $tl_document = $this->data['tl_document'];
    //     foreach ($tl_document as $key => $value) {
    //         $value;
    //     }

    //     Document::create([
    //         'document_library_id' => $library_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $trn_id          = DocumentLibrary::where('name', 'trn_cerftificate')->first()->id;
    //     $trn_certificate = $this->data['trn_certificate'];
    //     foreach ($trn_certificate as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $trn_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $third_party_id = DocumentLibrary::where('name', 'third_party_liability')->first()->id;
    //     $third_party    = $this->data['third_party_certificate'];
    //     foreach ($third_party as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $third_party_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $risk_id = DocumentLibrary::where('name', 'risk_assessement')->first()->id;
    //     $risk    = $this->data['risk_assessment'];
    //     foreach ($risk as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $risk_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $saftey_id = DocumentLibrary::where('name', 'safety_policy')->first()->id;
    //     $saftey    = $this->data['safety_policy'];
    //     foreach ($saftey as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $saftey_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $bank_id = DocumentLibrary::where('name', 'bank_details')->first()->id;
    //     $bank    = $this->data['bank_details'];
    //     foreach ($bank as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $bank_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);
    //     $authority_id = DocumentLibrary::where('name', 'authority_approval')->first()->id;
    //     $authority    = $this->data['authority_approval'];
    //     foreach ($authority as $key => $value) {
    //         $value;
    //     }
    //     Document::create([
    //         'document_library_id' => $authority_id,
    //         'url'                 => $value,
    //         'status'              => 'pending',
    //         'expiry_date'         => '2023-09-23',
    //         'accepted_by'         => $user,
    //         'documentable_id'     => $id,
    //         'documentable_type'   => 'App\Models\Vendor\Vendor',

    //     ]);

    // }


}
