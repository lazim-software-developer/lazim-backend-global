<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Models\Building\FlatTenant;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFlatTenant extends CreateRecord
{
    protected static string $resource = FlatTenantResource::class;
    protected function afterCreate()
    {
        FlatTenant::where('id', $this->record->id)
            ->update([
                'active' => 1,
            ]);

    }

}
