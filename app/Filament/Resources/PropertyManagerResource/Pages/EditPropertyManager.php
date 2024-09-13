<?php

namespace App\Filament\Resources\PropertyManagerResource\Pages;

use App\Filament\Resources\PropertyManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyManager extends EditRecord
{
    protected static string $resource = PropertyManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
