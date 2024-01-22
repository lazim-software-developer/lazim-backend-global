<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\Master\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('contract_type')
                            ->options([
                                'annual maintenance contract' => 'Annual Maintenance Contract',
                                'onetime' => 'OneTime',
                            ])
                            ->disabled()
                            ->searchable()
                            ->required()
                            ->label('Contract Type'),
                        Select::make('building_id')
                            ->relationship('building','name')
                            ->options(function (RelationManager $livewire) {
                                $buildingIds = DB::table('building_vendor')->where('vendor_id', $livewire->ownerRecord->id)->pluck('building_id')->toArray();
                                return Building::whereIn('id', $buildingIds)->pluck('name', 'id')->toArray();
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('service_id')
                            ->relationship('service','name')
                            ->options(function (RelationManager $livewire) {
                                $serviceIds = DB::table('service_vendor')->where('vendor_id', $livewire->ownerRecord->id)->pluck('service_id')->toArray();
                                return Service::whereIn('id', $serviceIds)->pluck('name', 'id')->toArray();
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabled()
                            ->placeholder('Service'),
                        DatePicker::make('start_date')
                            ->required()
                            ->rules(['date'])
                            ->minDate(function ($record, $state) {
                                if ($record?->start_date == null || $state != $record?->start_date) {
                                    return now()->format('Y-m-d');
                                }
                            })
                            // ->disabled()
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->required()
                            ->rules(['date'])
                            ->minDate(function ($record, $state) {
                                if ($record?->end_date == null || $state != $record?->end_date) {
                                    return now()->format('Y-m-d');
                                }
                            })
                            // ->disabled()
                            ->placeholder('End Date'),
                        FileUpload::make('document_url')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        TextInput::make('amount')
                            ->numeric(true)
                            ->minValue(1)
                            ->maxValue(1000000)
                            ->prefix('AED')
                            ->required(),
                        TextInput::make('budget_amount')
                            ->numeric(true)
                            ->minValue(1)
                            ->maxValue(1000000)
                            ->prefix('AED')
                            ->required(),
                        Hidden::make('vendor_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_type')->label('Contract Type'),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date'),
                Tables\Columns\TextColumn::make('end_date')->label('End Date'),
                Tables\Columns\TextColumn::make('amount')->label('Amount'),
                Tables\Columns\TextColumn::make('budget_amount')->label('Budget Amount'),
                // Tables\Columns\ImageColumn::make('document_url')->square()->disk('s3')->label('Document'),
                Tables\Columns\TextColumn::make('building.name')->label('Building'),
                Tables\Columns\TextColumn::make('service.name')->label('Service'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
