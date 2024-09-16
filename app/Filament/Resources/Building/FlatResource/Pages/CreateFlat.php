<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use App\Models\Building\Flat;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFlat extends CreateRecord
{
    protected static string $resource = FlatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_association_id'] ?? $data['owner_association_id'] = auth()->user()->owner_association_id;
        return $data;
    }

}
