<?php

namespace App\Filament\Resources\LedgersResource\Pages;

use App\Filament\Resources\LedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;

class ViewLedgers extends ViewRecord
{
    protected static string $resource = LedgersResource::class;

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
