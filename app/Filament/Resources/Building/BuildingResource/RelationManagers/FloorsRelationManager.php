<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Floor;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Building\Building;
use App\Forms\Components\FloorQrCode;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FloorsRelationManager extends RelationManager
{
    protected static string $relationship = 'floors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('floors')
                    ->disabled(fn (?Floor $record): bool => $record !== null && (request()->routeIs('*.view') || request()->routeIs('*.create')))
                    ->placeholder('Floors')
                    ->label('Floor')
                    ->maxLength(50)
                    ->rules(['max:50']),
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->disabled(fn (?Floor $record): bool => $record !== null && (request()->routeIs('*.edit') || request()->routeIs('*.create')))
                    ->searchable()
                    ->label('Building Name')
                    ->hidden(fn (string $operation): bool => $operation === 'create' || $operation === 'edit'),
                FloorQrCode::make('qr_code')
                    ->label('QR Code')
                    ->hidden(fn (string $operation): bool => $operation === 'create' || $operation === 'edit')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('floors')
            ->columns([
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('floors')->searchable()->label('Floor'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->after(function (Floor $record) {
                    $qrCodeContent = [
                        'floors' => $record->floors,
                        'building_id' => $record->building_id,
                    ];
                    // Generate a QR code using the QrCode library
                    $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                    // Update the qr_code field in the floors table
                    Floor::where('floors', $record->floors)
                    ->where('building_id', $record->building_id)
                    ->whereNull('qr_code')
                    ->update(['qr_code' => $qrCode]);

                    $floorCount = Floor::where('building_id', $record->building_id)->count();

                    Building::where('id', $record->building_id)
                    ->update(['floors' => $floorCount]);
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
