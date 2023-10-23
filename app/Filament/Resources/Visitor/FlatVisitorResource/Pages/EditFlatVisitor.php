<?php

namespace App\Filament\Resources\Visitor\FlatVisitorResource\Pages;

use App\Filament\Resources\Visitor\FlatVisitorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlatVisitor extends EditRecord
{
    protected static string $resource = FlatVisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
