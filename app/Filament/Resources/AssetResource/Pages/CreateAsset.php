<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Assets\Assetmaintenance;
use App\Models\Building\Building;
use App\Models\TechnicianAssets;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;
    // public function afterCreate(): void
    // {
    //     $qrCode = QrCode::size(200)->generate('Asset Name: '.$this->record->name."\n".'Location: '.$this->record->location);

    //     Asset::where('id', $this->record->id)->update(['qr_code' => $qrCode]);
    // }
    public function afterCreate(): void
    {
        // Fetch asset details from the database
        $asset = Asset::where('id', $this->record->id)->first();
        // Fetch technician_asset details 
        $technician_asset_id = TechnicianAssets::where('asset_id',$asset)->first();
        // Fetch Building name 
        $building_name = Building::where('id',$asset->building_id)->first();
        // Fetch maintenance details from the database
        $maintenance = Assetmaintenance::where('technician_asset_id', $technician_asset_id)->first();

        // Build an object with the required properties
        $qrCodeContent = [
            'id' => $this->record->id,
            'technician_asset_id' => $technician_asset_id,
            'asset_id' => $this->record->id,
            'asset_name' => $asset->name,
            'maintenance_status' => 'not-started',
            'building_name' => $building_name->name,
            'building_id' => $asset->building_id,
            'location' => $asset->location,
            'description' => $asset->description,
            // 'last_service_on' => $maintenance->maintenance_date,
        ];

        // Generate a QR code using the QrCode library
        $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));

        // Update the newly created asset record with the generated QR code
        Asset::where('id', $this->record->id)->update(['qr_code' => $qrCode]);
    }

}
