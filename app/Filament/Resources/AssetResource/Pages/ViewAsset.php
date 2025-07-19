<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('qr code')->label('Print QR Code')
            ->action(function ($record) {
                $data = [
                    'qr_code' => $record->qr_code,
                    'asset_code' => $record->asset_code,
                    'is_property_manager' => in_array(auth()->user()->role->name, ['Property Manager', 'Admin'])
                ];
                return redirect('/qr_code')->with('data', $data);
            })
        ];
    }
}
