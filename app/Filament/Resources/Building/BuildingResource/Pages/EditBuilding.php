<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Models\Floor;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Filament\Resources\Building\BuildingResource;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        if ($this->record->floors != null) {
            // Build an object with the required properties
            $qrCodeContent = [
                'floors' => $this->record->floors,
                'building_id' => $this->record->id,
            ];
            // Generate a QR code using the QrCode library
            $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
            Floor::create([
                'floors' => $this->record->floors,
                'building_id' => $this->record->id,
                'qr_code' => $qrCode,
            ]);
        }
    }
}
