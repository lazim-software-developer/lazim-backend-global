<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditFlat extends EditRecord
{
    protected static string $resource = FlatResource::class;

    public function getFormActions(): array
    {
        return [
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
