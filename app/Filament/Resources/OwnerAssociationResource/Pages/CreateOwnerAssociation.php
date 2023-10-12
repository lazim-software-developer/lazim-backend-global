<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use App\Filament\Resources\OwnerAssociationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnerAssociation extends CreateRecord
{
    protected ?string $heading        = 'Owner Association';

    protected static string $resource = OwnerAssociationResource::class;
}
