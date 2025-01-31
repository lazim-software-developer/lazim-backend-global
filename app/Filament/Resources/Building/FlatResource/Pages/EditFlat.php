<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlat extends EditRecord
{
    protected static string $resource = FlatResource::class;
    protected ?string $heading = 'Flat';

    public $value;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
