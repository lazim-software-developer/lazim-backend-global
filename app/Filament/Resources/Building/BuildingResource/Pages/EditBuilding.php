<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Models\Floor;
use Filament\Actions;
use Filament\Actions\Action;
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
            // Action::make('Inhouse services')
            //     ->label('Inhouse services')
            //     ->url(BuildingResource::getUrl('services'))
        ];
    }
    public function afterSave()
    {
        if ($this->record->floors != null && Floor::where('building_id', $this->record->id)->count() === 0) {
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
