<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnaggings extends ListRecords
{
    protected static string $resource = SnaggingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
