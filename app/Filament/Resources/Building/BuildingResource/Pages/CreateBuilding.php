<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use App\Models\Floor;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['show_inhouse_services'] = 0;
        $data['managed_by']        = 'Property Manager';
        return $data;
    }

    protected function afterCreate()
    {
        if($this->record->floors != null){
             $countfloor = $this->record->floors;
            while ($countfloor > 0) {
                // Build an object with the required properties
                $qrCodeContent = [
                    'floors' => $countfloor,
                    'building_id' => $this->record->id,
                ];
                // Generate a QR code using the QrCode library
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                Floor::create([
                    'floors' => $countfloor,
                    'building_id' => $this->record->id,
                    'qr_code' => $qrCode,
                ]);
                $countfloor = $countfloor - 1;
            }
        }
    }
}
