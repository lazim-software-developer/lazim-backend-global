<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use App\Models\TechnicianAssets;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetMaintenance extends ViewRecord
{
    protected static string $resource = AssetMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $user = User::find($data['maintained_by']);
        $technicianAsset = TechnicianAssets::find($data['technician_asset_id']);
        
        $data['maintained_by'] = $user->first_name;
        $data['asset'] = $technicianAsset->asset->name;
        $data['technician'] = $technicianAsset->user->first_name;
        $data['vendor'] = $technicianAsset->vendor->name;
        // dd($data);
     
        return $data;
    }
}
