<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use ZipArchive;
use Filament\Forms;
use Filament\Tables;
use App\Models\Floor;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Building\Building;
use App\Forms\Components\FloorQrCode;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                    Tables\Actions\BulkAction::make('generate_all_qr_codes')
                            ->label('Download All QR Codes')
                            ->action(function ($records) {
                                try {
                                    $floors = $records->count() ? $records : null;

                                    if (!isset($floors) && empty($floors)) {
                                        Notification::make()
                                            ->title('Error')
                                            ->body('No buildings found to generate QR codes.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    // Create a temporary file for the ZIP
                                    $buildingName = $floors->count() ? $floors->first()->building->name : 'all_buildings';
                                    $zipFileName = $buildingName.'_qr_codes_' . time() . '.zip';
                                    $zipFilePath = storage_path('app/temp/' . $zipFileName);
                                    $zip = new ZipArchive();

                                    // Ensure the temp directory exists
                                    Storage::makeDirectory('temp');

                                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                                        throw new \Exception('Failed to create ZIP file.');
                                    }

                                    // Generate QR code for each building
                                    foreach ($floors as $floor) {
                                        $qrCodeContent = [
                                            'floors' => $floor->floors,
                                            'building_id' => $floor->building_id,
                                            'building_name' => $buildingName,
                                        ];
                                        $qrCode = QrCode::format('png')
                                            ->size(500)
                                            ->errorCorrection('H')
                                            ->margin(4)
                                            ->generate(json_encode($qrCodeContent));

                                        // Add QR code to ZIP
                                        $qrFileName = 'floor_' . $floor->floors . '.png';
                                        $zip->addFromString($qrFileName, $qrCode);

                                    }

                                    $zip->close();

                                    // Stream the ZIP file for download
                                    $response = response()->download($zipFilePath, $buildingName.'_qr_codes.zip', [
                                        'Content-Type' => 'application/zip',
                                    ]);

                                    // Delete the temporary file after download
                                    register_shutdown_function(function () use ($zipFilePath) {
                                        if (file_exists($zipFilePath)) {
                                            unlink($zipFilePath);
                                        }
                                    });

                                    return $response;
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Failed to generate QR codes: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-qr-code')
                            ->requiresConfirmation()
                            ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
