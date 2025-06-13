<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use ZipArchive;
use Filament\Forms;
use Filament\Tables;
use App\Models\Floor;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LocationQrCode;
use App\Models\Building\Building;
use App\Forms\Components\FloorQrCode;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Filament\Resources\RelationManagers\RelationManager;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class LocationQrCodeRelationManager extends RelationManager
{
    protected static string $relationship = 'LocationQrCode';
    

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                Select::make('floor_id')
                    ->options(Floor::where('building_id', $this->ownerRecord->id)->pluck('floors', 'id')->toArray())
                    ->disabled(fn (?LocationQrCode $record): bool => $record !== null && (request()->routeIs('*.view') || request()->routeIs('*.create')))
                    ->placeholder('Floors')
                    ->label('Floor')
                    ->searchable()
                    ->rules(['required']),
                TextInput::make('floor_name')
                    ->disabled(fn (?LocationQrCode $record): bool => $record !== null && (request()->routeIs('*.view') || request()->routeIs('*.create')))
                    ->placeholder('Location Name')
                    ->label('Location Name')
                    ->maxLength(50)
                    ->rules(['required','max:50',function (?LocationQrCode $record) {
                        return function (string $attribute, $value, \Closure $fail) use ($record){
                            $exists = LocationQrCode::where('building_id',$this->ownerRecord->id)->where('floor_name',$value)->exists();
                            if($record === null && $exists){
                                $fail('The Entered Location already Exists!');
                            }
                            if($record != null && LocationQrCode::whereNot('id',$record->id)->where('building_id',$this->ownerRecord->id)->where('floor_name',$value)->exists()) {
                                $fail('The Entered Location already Exists!');
                            }
                        };

                    }]),
                TextInput::make('code')
                    ->disabled(fn (?LocationQrCode $record): bool => $record !== null && (request()->routeIs('*.view') || request()->routeIs('*.create')))
                    ->placeholder('Unique Code')
                    ->label('Unique Code')
                    ->maxLength(50),
                FloorQrCode::make('qr_code')
                    ->label('QR Code')
                    ->formatStateUsing(function ($record) {
                        if (!$record) {
                            return null;
                        }
                        
                        // If QR code is already stored in database, use it
                        if ($record->qr_code) {
                            return '<img src="data:image/png;base64,' . $record->qr_code . '" alt="QR Code" style="max-width: 200px; height: auto;" />';
                        }
                        
                        // Otherwise generate it dynamically
                        $qrData = json_encode([
                            'floors' => $record->floor_id,
                            'building_id' => $record->building_id,
                            'code' => $record->code
                        ]);
                        
                        $qrCode = QrCode::size(200)->format('png')->generate($qrData);
                        $qrCodeBase64 = base64_encode($qrCode);
                        
                        return '<img src="data:image/png;base64,' . $qrCodeBase64 . '" alt="QR Code" style="max-width: 200px; height: auto;" />';
                    })
                    ->html()
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
                            'floors' => $record->floor_id,
                            'building_id' => $record->building_id,
                            'code'=>$record->code
                        ];
                        $qrCode = QrCode::format('png')->size(200)->generate(json_encode($qrCodeContent));
                        return 'data:image/png;base64,' . base64_encode($qrCode);
                    }),
                TextColumn::make('floor_name')->searchable()->label('Location Name'),
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('floor.floors')->searchable()->label('Floor'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (LocationQrCode $record) {
                        // Update the floor count for the building
                        $building = Building::find($record->building_id);
                        $floor = Floor::find($record->floor_id);
                        if($building->code){
                            $code = $building->code.'/'.$floor->floors.'/'.$record->floor_name;
                        }else{
                            $words = explode(' ', $building->name);
                            $buildingcode = '';
                            foreach($words as $word){
                                $buildingcode .= substr($word, 0, 1);
                            }
                            $code = $buildingcode.'/'.$floor->floors.'/'.$record->floor_name;
                        }
                        $qrCodeContent = [
                            'floor_id' => $record->floor_id,
                            'building_id' => $record->building_id,
                            'code' => $code
                        ];
                        $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                        LocationQrCode::where('id', $record->id)
                            ->update(['qr_code' => $qrCode,'code'=>$code]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_qr')
                    ->label('Download QR Code')
                    ->action(function (LocationQrCode $record) {
                        $qrCodeContent = [
                            'floors' => $record->floor_id,
                            'building_id' => $record->building_id,
                            'code' => $record->code
                        ];
                        // Generate QR code as PNG
                        $qrCode = QrCode::format('png')->size(500) // Increased size for better quality
                            ->errorCorrection('H') // High error correction
                            ->margin(4)->generate(json_encode($qrCodeContent));

                        // Return the QR code as a downloadable PNG
                        return response()->streamDownload(function () use ($qrCode) {
                            echo $qrCode;
                        }, 'floor_' . $record->floor_id . '.png', [
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
                                            'floors' => $floor->floor_id,
                                            'building_id' => $floor->building_id,
                                            'building_name' => $buildingName,
                                            'code' => $floor->code
                                        ];
                                        $qrCode = QrCode::format('png')
                                            ->size(500)
                                            ->errorCorrection('H')
                                            ->margin(4)
                                            ->generate(json_encode($qrCodeContent));

                                        // Add QR code to ZIP
                                        $qrFileName = 'floor_' . $floor->floor_id . '.png';
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
