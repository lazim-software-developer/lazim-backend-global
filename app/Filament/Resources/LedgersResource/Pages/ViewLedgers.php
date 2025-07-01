<?php

namespace App\Filament\Resources\LedgersResource\Pages;

use App\Filament\Resources\LedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;

class ViewLedgers extends ViewRecord
{
    protected static string $resource = LedgersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ...
        ])
        ->actions([
            // Tables\Actions\ViewAction::make(),
            // ...
        ]);
}
}
