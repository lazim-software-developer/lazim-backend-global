<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Models\Building\FlatTenant;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFlatTenant extends CreateRecord
{
    protected static string $resource = FlatTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
    protected function afterCreate()
    {
        FlatTenant::where('id', $this->record->id)
            ->update([
                'active' => 1,
            ]);

    }

}
