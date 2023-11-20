<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;
    public function afterCreate(): void
    {
        $qrCode = QrCode::size(200)->generate('Asset Name: '.$this->record->name."\n".'Location: '.$this->record->location);

        Asset::where('id', $this->record->id)->update(['qr_code' => $qrCode]);
    }
}
