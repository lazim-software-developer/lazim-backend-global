<?php

namespace App\Filament\Resources\Building\BuildingResource\Pages;

use App\Models\Floor;
use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Filament\Resources\Building\BuildingResource;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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

                $exists = Floor::where('floors', $countfloor)
                    ->where('building_id', $this->record->id)
                    ->exists();

                if (!$exists) {
                    // Generate a QR code using the QrCode library
                    $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                    Floor::create([
                        'floors' => $countfloor,
                        'building_id' => $this->record->id,
                        'qr_code' => $qrCode,
                    ]);
                }
                $countfloor = $countfloor - 1;
            }
        }

        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        $created_by = $connection->table('users')->where('owner_association_id', $this->record->owner_association_id)->where('type', 'company')->first()?->id;
        $connection->table('users')->updateOrInsert([
            'building_id' => $this->record->id,
            'owner_association_id' => $this->record->owner_association_id,
        ], [
            'name' => $this->record->name,
        ]);
    }
}
