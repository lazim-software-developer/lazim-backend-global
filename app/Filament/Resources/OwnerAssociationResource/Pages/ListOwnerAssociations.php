<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use App\Filament\Resources\OwnerAssociationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOwnerAssociations extends ListRecords
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading        = 'Owner Association';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
