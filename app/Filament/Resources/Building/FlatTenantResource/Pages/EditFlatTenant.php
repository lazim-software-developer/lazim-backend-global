<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlatTenant extends EditRecord
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title = 'Resident';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
