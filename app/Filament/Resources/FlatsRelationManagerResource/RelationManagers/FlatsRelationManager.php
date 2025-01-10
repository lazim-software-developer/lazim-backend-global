<?php

namespace App\Filament\Resources\FlatsRelationManagerResource\RelationManagers;

use App\Imports\FlatImport;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class FlatsRelationManager extends RelationManager
{
    protected static string $relationship = 'flats';

    protected static ?string $title = 'Flats';

    protected static ?string $modelLabel = 'Flat';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        TextInput::make('property_number')
                            ->label('Unit Number')
                            ->required()
                            ->alphaDash()
                            ->placeholder('Unit Number'),
                        // Select::make('owner_association_id')
                        //     ->required()
                        //     ->options(function(){
                        //         return OwnerAssociation::where('role','Property Manager')->pluck('name','id');
                        //     })
                        //     ->visible(auth()->user()->role->name === 'Admin')
                        //     ->live()
                        //     ->preload()
                        //     ->searchable()
                        //     ->label('Select Property Manager'),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->options(function (Get $get) {
                                $buildings = DB::table('building_owner_association')
                                    ->where('owner_association_id', $this->ownerRecord->id)
                                    ->pluck('building_id');
                                return Building::whereIn('id', $buildings)->pluck('name', 'id');
                            })
                            ->reactive()
                            ->preload()
                            ->required()
                            ->searchable()
                            ->placeholder('Building')
                            ->label('Select Building'),
                        Select::make('property_type')
                            ->options([
                                'Shop'   => 'Shop',
                                'Office' => 'Office',
                                'Unit'   => 'Unit',
                            ])
                            ->required()
                            ->searchable(),
                        TextInput::make('suit_area')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('actual_area')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('balcony_area')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('applicable_area')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('virtual_account_number')
                            ->placeholder('NA')
                            ->hidden(in_array(auth()->user()->role->name, ['Property Manager', 'Admin']))
                            ->numeric(),
                        TextInput::make('parking_count')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('plot_number')
                            ->placeholder('NA')
                            ->numeric(),
                        TextInput::make('makhani_number')
                            ->placeholder('NA')
                            ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                            ->numeric(),
                        TextInput::make('dewa_number')
                            ->placeholder('NA')
                            ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                            ->numeric(),
                        TextInput::make('etisalat/du_number')
                            ->label('BTU/Etisalat Number')
                            ->placeholder('NA')
                            ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                            ->numeric(),
                        TextInput::make('btu/ac_number')
                            ->placeholder('NA')
                            ->label('BTU/AC Number')
                            ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                            ->numeric(),
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('property_number')
            ->columns([
                TextColumn::make('property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('suit_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->suit_area)
                        ? number_format((float) $record->suit_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('actual_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->suit_area)
                        ? number_format((float) $record->suit_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('balcony_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->suit_area)
                        ? number_format((float) $record->suit_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->formatStateUsing(fn($record) => is_numeric($record->suit_area)
                        ? number_format((float) $record->suit_area, 2)
                        : 'NA')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
                    ->visible(!in_array(auth()->user()->role->name, ['Property Manager', 'Admin']))
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
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->options(function () {
                        $buildings = DB::table('building_owner_association')
                            ->where('owner_association_id', $this->ownerRecord->id)
                            ->pluck('building_id');
                        return Building::whereIn('id', $buildings)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload(),

                SelectFilter::make('property_type')
                    ->options([
                        'Shop'   => 'Shop',
                        'Office' => 'Office',
                        'Unit'   => 'Unit',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->label('New Flat'),

                Action::make('feature')
                    ->label('Upload Flats') // Set a label for your action
                    ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                    ->form([
                        // Select::make('owner_association_id')
                        //     ->options(function () {
                        //         return OwnerAssociation::where('role', 'Property Manager')->pluck('name', 'id');
                        //     })
                        //     ->visible(auth()->user()->role->name === 'Admin')
                        //     ->required()
                        //     ->live()
                        //     ->preload()
                        //     ->searchable()
                        //     ->label('Select Property Manager'),
                        Select::make('building_id')
                            ->options(function () {
                                $buildings = DB::table('building_owner_association')
                                    ->where('owner_association_id', $this->ownerRecord->id)
                                    ->pluck('building_id');
                                return Building::whereIn('id', $buildings)->pluck('name', 'id');
                            })
                            ->required()
                            ->live()
                            ->preload()
                            ->searchable()
                            ->label('Select Building'),
                        FileUpload::make('excel_file')
                            ->label('Upload File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                                'application/vnd.ms-excel', // for .xls
                            ])
                            ->required()
                            ->disk('local') // or your preferred disk
                            ->directory('budget_imports'), // or your preferred directory
                    ])
                    ->action(function ($record, array $data, $livewire) {

                        $filePath   = $data['excel_file'];
                        $fullPath   = storage_path('app/' . $filePath);
                        $oaId       = $this->ownerRecord->id;
                        $buildingId = $data['building_id'];

                        if (!file_exists($fullPath)) {
                            Log::error("File not found at path: ", [$fullPath]);
                        }

                        // Now import using the file path
                        Excel::import(new FlatImport($oaId, $buildingId), $fullPath); // Notify user of success
                    }),
                ExportAction::make('exporttemplate')->exports([
                    ExcelExport::make()
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                        ->withColumns([
                            Column::make('unit_number'),
                            Column::make('property_type'),
                            Column::make('mollak_property_id'),
                            Column::make('suit_area'),
                            Column::make('actual_area'),
                            Column::make('balcony_area'),
                            Column::make('applicable_area'),
                            Column::make('parking_count'),
                            Column::make('makhani_number'),
                            Column::make('dewa_number'),
                            Column::make('etisalat/du_number'),
                            Column::make('btu/ac_number'),
                        ]),
                ])
                    ->visible(in_array(auth()->user()->role->name, ['Admin', 'Property Manager']))
                    ->label('Download sample file'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateDescription('Create or Upload a Flat to get started.')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
