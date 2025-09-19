<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Log;
use ZipArchive;
use Filament\Tables;
use App\Models\Floor;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LocationQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
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
                    ->hidden(fn (string $operation): bool => $operation === 'create' || $operation === 'edit')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('floors')
            ->columns([
                TextColumn::make('floor_name')->searchable()->label('Location Name'),
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('floor.floors')->searchable()->label('Floor'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Location QR Code')
                    ->icon('heroicon-o-plus')
                    ->after(function (LocationQrCode $record) {
                        // Update the location qr code and code after creation
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
                        $qrCode = self::generateLocationQrCode($record, 'svg', 500, 500);
                        LocationQrCode::where('id', $record->id)
                            ->update(['qr_code' => $qrCode,'code'=>$code]);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
                        Tables\Actions\Action::make('download_qr')
                            ->action(function (LocationQrCode $record) {

                                $qrImage = $record->qr_code ? $record->qr_code : self::generateLocationQrCode($record);
                                return response()->streamDownload(function () use ($qrImage) {
                                    echo $qrImage;
                                }, 'location_' . $record->floor_name . '.svg', [
                                    'Content-Type' => 'image/svg',
                                ]);
                            })
                            ->icon('heroicon-o-arrow-down-tray')
                            ->requiresConfirmation(false)
                            ->tooltip('Download QR Code'),
                        Tables\Actions\Action::make('regenerate_qr')
                            ->label('Regenerate QR Code')
                            ->action(function (LocationQrCode $record) {
                                try {
                                    $qrImage = self::generateLocationQrCode($record);
                                    LocationQrCode::where('id', $record->id)
                                        ->update(['qr_code' => $qrImage]);
                                    Notification::make()
                                        ->title('QR Code Regenerated')
                                        ->body('The QR code has been successfully regenerated.')
                                        ->success()
                                        ->send();
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Failed to regenerate QR code: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-arrow-path')
                            ->requiresConfirmation()
                            ->tooltip('Regenerate QR Code'),
                // Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('generate_all_qr_codes')
                            ->label('Download All QR Codes')
                            ->action(function ($records) {
                                try {
                                    $locations = $records->count() ? $records : null;
                                    if (!isset($locations) && empty($locations)) {
                                        Notification::make()
                                            ->title('Error')
                                            ->body('No buildings found to generate QR codes.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    // Create a temporary file for the ZIP
                                    $buildingName = $locations->count() ? $locations->first()->building->name : 'all_buildings';
                                    $zipFileName = $buildingName.'_qr_codes_' . time() . '.zip';
                                    $zipFilePath = storage_path('app/temp/' . $zipFileName);
                                    $zip = new ZipArchive();

                                    // Ensure the temp directory exists
                                    Storage::makeDirectory('temp');

                                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                                        throw new \Exception('Failed to create ZIP file.');
                                    }

                                    // Generate QR code for each building
                                    foreach ($locations as $location) {
                                        $qrImage = self::generateLocationQrCode($location);

                                        // Add QR code to ZIP
                                        $qrFileName = 'location_' . $location->floor_name . '.svg';
                                        $zip->addFromString($qrFileName, $qrImage);

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
                    Tables\Actions\BulkAction::make('generate_qr_codes_in_pdf')->label('Download PDF')
                            ->action(function($records) {
                                // Log::info('Generating PDF for QR Codes', ['data' => $records]);
                                $pdf = Pdf::loadView('filament.custom.location-qr-pdf', compact('records'));
                                    return response()->streamDownload(
                                        fn() => print($pdf->output()),
                                        'Qr_Codes_' . $records->first()->building->name .'.pdf'
                                    );
                            })
                            ->icon('heroicon-o-document-arrow-down'),
                    Tables\Actions\BulkAction::make('regenerate_all_qr_codes')
                            ->label('Regenerate All QR Codes')
                            ->action(function ($records) {
                                try {
                                    $locations = $records->count() ? $records : null;
                                    if (!isset($locations) && empty($locations)) {
                                        Notification::make()
                                            ->title('Error')
                                            ->body('No buildings found to generate QR codes.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    // Generate QR code for each building
                                    foreach ($locations as $location) {
                                        \Log::info('Regenerating QR Code for Location ID: ' . $location->id);
                                        $qrImage = self::generateLocationQrCode($location);

                                        LocationQrCode::where('id', $location->id)
                                            ->update(['qr_code' => $qrImage]);
                                    }
                                    return Notification::make()
                                            ->title('QR Code Regenerated')
                                            ->body('The QR code has been successfully regenerated.')
                                            ->success()
                                            ->send();
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Failed to Regenerate QR codes: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-arrow-path')
                            ->requiresConfirmation()
                            ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public function generateLocationQrCode(LocationQrCode $record, $type='svg', $qrCodeSize = 500, $width = 200): string
    {
        $qrCodeContent = [
            'location_id' => $record->id,
            'floors' => $record->floor_id,
            'building_id' => $record->building_id,
            'code' => $record->code ?? $record->floors,
        ];

        $height = $qrCodeSize + 100; // Enough space for QR code and text
        $qrCode = QrCode::format($type)
            ->size($qrCodeSize)
            ->errorCorrection('H')
            ->margin(4)
            ->generate(json_encode($qrCodeContent));
        $qrText[] = $qrCodeContent['code'] ?? ' ';

        $qrImage = addTextToQR($qrCode, $qrText, $qrCodeSize, $width, $height);
                // Convert SVG to PNG
        $pngPath = storage_path('app/public/qr_codes/' . $record->floor_name . '-' . $record->code . '-' . uniqid() . '.png');
        $pngRelativePath = str_replace(storage_path('app/public'), '/storage', $pngPath);
        convertSvgToPng($qrImage->toHtml(), $pngPath, 500);

        return $pngRelativePath;
    }
}
