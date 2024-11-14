<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Filament\Resources\Building\BuildingResource;
use App\Models\Floor;
use DB;
use Filament\Resources\Pages\EditRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(array_key_exists('search',$data)){
            $data['address'] = $data['search'];
        }
        if (auth()->user()->role->name == 'Property Manager') {
            DB::table('building_owner_association')
                ->where('building_id', $this->record->id)
                ->update([
                    'from' => $data['from'],
                    'to'   => $data['to'],
                ]);
        }
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($this->record) && !empty($this->record->address)) {
            $data['search'] = $this->record->address;
        } else {
            $data['search'] = null;
        }

        if (auth()->user()->role->name == 'Property Manager') {
            $data['from'] = DB::table('building_owner_association')
                ->where('building_id', $this->record->id)
                ->first()->from;
            $data['to'] = DB::table('building_owner_association')
                ->where('building_id', $this->record->id)
                ->first()->to;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
