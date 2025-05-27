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
use Filament\Tables\Columns\ImageColumn;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Filament\Resources\RelationManagers\RelationManager;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

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
                ImageColumn::make('qr_code')
                    ->label('QR Code')
                    ->getStateUsing(function ($record) {
                        $qrCodeContent = [
                            'floors' => $record->floors,
                            'building_id' => $record->building_id,
                        ];
                        $qrCode = QrCode::format('png')->size(200)->generate(json_encode($qrCodeContent));
                        return 'data:image/png;base64,' . base64_encode($qrCode);
                    }),
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('floors')->searchable()->label('Floor'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Floor $record) {
                        // Update the floor count for the building
                        $floorCount = Floor::where('building_id', $record->building_id)->count();
                        Building::where('id', $record->building_id)
                            ->update(['floors' => $floorCount]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_qr')
                    ->label('Download QR Code')
                    ->action(function (Floor $record) {
                        $qrCodeContent = [
                            'floors' => $record->floors,
                            'building_id' => $record->building_id,
                        ];
                        // Generate QR code as PNG
                        $qrCode = QrCode::format('png')->size(500) // Increased size for better quality
                            ->errorCorrection('H') // High error correction
                            ->margin(4)->generate(json_encode($qrCodeContent));

                        // Return the QR code as a downloadable PNG
                        return response()->streamDownload(function () use ($qrCode) {
                            echo $qrCode;
                        }, 'floor_' . $record->floors . '.png', [
                            'Content-Type' => 'image/png',
                        ]);
                    })
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation(false),
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
