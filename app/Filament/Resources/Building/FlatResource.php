<?php

namespace App\Filament\Resources\Building;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Tables\Actions\Action;
use App\Filament\Imports\UnitImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Forms\Components\BelongsToSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Building\FlatResource\Pages;
use App\Filament\Resources\FlatResource\Pages\ViewFlat;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\Building\FlatResource\RelationManagers;

class FlatResource extends Resource
{
    protected static ?string $model = Flat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Units';
    protected static ?string $navigationGroup = 'Flat Management';
    protected static ?string $tenantRelationshipName = 'flats';
    public static function getSlug(): string
    {
        return 'flats';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        TextInput::make('floor')->label('Floor')
                            ->required()
                            ->placeholder('Floor'),
                        Hidden::make('owner_association_id')
                            ->default(auth()->user()?->owner_association_id),
                        TextInput::make('property_number')->label('Property Number')
                            ->required()
                            ->placeholder('Property Number'),
                        TextInput::make('property_type')->label('Property Type')
                            ->required()
                            ->placeholder('Property Type'),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name', function ($query) {
                                $query->where('owner_association_id', auth()->user()->owner_association_id);
                            })
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        TextInput::make('suit_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('actual_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('balcony_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('applicable_area')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('virtual_account_number')
                            ->default('NA')->placeholder('NA'),
                        TextInput::make('parking_count')
                            ->placeholder('Parking Count')
                            ->numeric()
                            ->rules([
                                'numeric',
                                'min:0',
                                'integer',
                                'regex:/^[0-9]+$/'
                            ])
                            ->minValue(0)
                            ->maxValue(999),
                        TextInput::make('plot_number')
                            ->placeholder('Plot Number')
                            ->numeric()
                            ->rules([
                                'numeric',
                                'min:0',
                                'integer',
                                'regex:/^[0-9]+$/'
                            ])
                            ->minValue(0)
                            ->maxValue(999),
                        Toggle::make('status')
                            ->rules(['boolean'])
                            ->label('Status'),
                        Hidden::make('created_by')
                            ->default(auth()->user()?->id),
                        Hidden::make('updated_by')
                            ->default(auth()->user()?->id),
                        Hidden::make('resource')
                            ->default('Lazim'),
                        MarkdownEditor::make('description')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->label('About')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('resource')
                    ->default('NA')
                    ->searchable()
                    ->label('Resource'),
                TextColumn::make('floor')
                    ->sortable()
                    ->default('NA')
                    ->searchable()
                    ->label('Flat'),
                TextColumn::make('property_number')
                    ->sortable()
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('suit_area')
                    ->formatStateUsing(function ($record) {
                        if ($record->suit_area === 'NA') {
                            return 'NA';
                        }

                        return is_numeric($record->suit_area)
                            ? number_format($record->suit_area, 2)
                            : $record->suit_area;
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('actual_area')
                    ->formatStateUsing(function ($record) {
                        if ($record->actual_area === 'NA') {
                            return 'NA';
                        }

                        return is_numeric($record->actual_area)
                            ? number_format($record->actual_area, 2)
                            : $record->actual_area;
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('balcony_area')
                    ->formatStateUsing(function ($record) {
                        if ($record->balcony_area === 'NA') {
                            return 'NA';
                        }

                        return is_numeric($record->balcony_area)
                            ? number_format($record->balcony_area, 2)
                            : $record->balcony_area;
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->formatStateUsing(function ($record) {
                        if ($record->applicable_area === 'NA') {
                            return 'NA';
                        }

                        return is_numeric($record->applicable_area)
                            ? number_format($record->applicable_area, 2)
                            : $record->applicable_area;
                    })
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('parking_count')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('plot_number')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tenants.role')
                    ->label('Occupied By')
                    ->default('NA')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('delete')
                    ->button()
                    ->action(function ($record,) {
                        $record->delete();

                        Notification::make()
                            ->title('Flat Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->withColumns([
                                Column::make('created_by')
                                    ->heading('Created By')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->CreatedBy->first_name . ' ' . $record->CreatedBy->last_name ?? 'N/A'
                                    ),
                                // Custom column using relationship
                                Column::make('owner_association_id')
                                    ->heading('Owner Association Name')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->ownerAssociation->name ?? 'N/A'
                                    ),
                                Column::make('building_id')
                                    ->heading('Building Name')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->building->name ?? 'N/A'
                                    ),
                                Column::make('floor')
                                    ->heading('Floor'),
                                Column::make('property_number')
                                    ->heading('Property Number'),
                                Column::make('property_type')
                                    ->heading('Property Type'),
                                Column::make('suit_area')
                                    ->heading('Suit Area'),
                                Column::make('actual_area')
                                    ->heading('Actual Area'),
                                Column::make('actual_area')
                                    ->heading('Actual Area'),
                                Column::make('balcony_area')
                                    ->heading('Balcony Area'),
                                Column::make('applicable_area')
                                    ->heading('Applicable Area'),
                                Column::make('virtual_account_number')
                                    ->heading('Virtual Account Number'),
                                Column::make('parking_count')
                                    ->heading('Parking Count'),
                                Column::make('plot_number')
                                    ->heading('Plot Number'),
                                // Formatted date with custom accessor
                                Column::make('created_at')
                                    ->heading('Created Date')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        $state ? $state->format('d/m/Y') : ''
                                    ),
                                Column::make('status')
                                    ->heading('Status')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->status == 1
                                            ? 'Active'
                                            : 'Inactive'
                                    ),

                                // Created by user info
                                // Column::make('created_by_name')
                                //     ->heading('Created By')
                                //     ->formatStateUsing(fn ($record) =>
                                //         $record->createdBy->name ?? 'System'
                                //     ),
                            ])
                            ->withFilename(date('Y-m-d') . '-flat-report')
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                    ]),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Unit')
                    ->form([
                        Section::make()
                            ->schema([
                                View::make('filament.components.sample-download-link')
                                    ->view('filament.components.sample-flat-file-download'),
                                FileUpload::make('file')
                                    ->label('Choose CSV File')
                                    ->disk('local')
                                    ->directory('temp-imports')
                                    ->acceptedFileTypes([
                                        'text/csv',
                                        'text/plain',
                                        'application/csv',
                                    ])
                                    ->maxSize(5120)
                                    ->required()
                                    ->helperText('Upload your CSV file in the correct format')
                            ])
                    ])
                    ->action(function (array $data) {
                        try {
                            $import = new UnitImport();
                            Excel::import($import, $data['file']);

                            $result = $import->getResultSummary();

                            if ($result['status'] === 200) {
                                // Generate detailed report
                                $report = "Import Report " . now()->format('Y-m-d H:i:s') . "\n\n";
                                $report .= "Successfully imported: {$result['imported']}\n";
                                $report .= "Skipped (already exists): {$result['skip']}\n";
                                $report .= "Errors: {$result['error']}\n\n";

                                // Add detailed error and skip information
                                foreach ($result['details'] as $detail) {
                                    $report .= "Row {$detail['row_number']}: {$detail['message']}\n";
                                    $report .= "Data: " . json_encode($detail['data']) . "\n\n";
                                }

                                // Save report
                                $filename = 'flat-import-' . now()->format('Y-m-d-H-i-s') . '.txt';
                                $reportPath = 'import-reports/' . $filename;
                                Storage::disk('local')->put($reportPath, $report);
                            }
                            if ($result['status'] === 401) {
                                Notification::make()
                                    ->title('invalid File')
                                    ->body("{$result['error']}")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            } else {
                                // Show notification with results
                                Notification::make()
                                    ->title('Import Complete')
                                    ->body(
                                        collect([
                                            "Successfully imported: {$result['imported']}",
                                            "Skipped: {$result['skip']}",
                                            "Errors: {$result['error']}"
                                        ])->join("\n")
                                    )
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('download_report')
                                            ->label('Download Report')
                                            ->url(route('download.import.report', ['filename' => $filename]))
                                            ->openUrlInNewTab()
                                    ])
                                    ->success()
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }

                        // Clean up temporary file
                        Storage::disk('local')->delete($data['file']);
                    })
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // FlatResource\RelationManagers\FlatDomesticHelpRelationManager::class,
            // FlatResource\RelationManagers\FlatTenantRelationManager::class,
            // FlatResource\RelationManagers\FlatVisitorRelationManager::class,
            // FlatResource\RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateFlat::route('/create'),
            'index' => Pages\ListFlats::route('/'),
            'view' => ViewFlat::route('/{record}'),
            'edit' => Pages\EditFlat::route('/{record}/edit'),
        ];
    }
}
