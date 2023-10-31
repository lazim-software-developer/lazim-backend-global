<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use App\Filament\Resources\User\OwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;

class EditOwner extends EditRecord
{
    protected static string $resource = OwnerResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
