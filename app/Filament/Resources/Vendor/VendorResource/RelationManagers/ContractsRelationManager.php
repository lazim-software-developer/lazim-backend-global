<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\Master\Service;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

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
                                'Annual Maintenance Contract' => 'Annual Maintenance Contract',
                                'OneTime' => 'OneTime',
                            ])
                            ->searchable()
                            ->required()
                            ->label('Contract Type'),
                        Select::make('building_id')
                            ->options(function (RelationManager $livewire) {
                                $buildingIds = DB::table('building_vendor')->where('vendor_id', $livewire->ownerRecord->id)->pluck('building_id')->toArray();
                                return Building::whereIn('id', $buildingIds)->pluck('name', 'id')->toArray();
                            })
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('service_id')
                            ->options(function (RelationManager $livewire) {
                                $serviceIds = DB::table('service_vendor')->where('vendor_id', $livewire->ownerRecord->id)->pluck('service_id')->toArray();
                                return Service::whereIn('id', $serviceIds)->pluck('name', 'id')->toArray();
                            })
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Service'),
                        DatePicker::make('start_date')
                            ->rules(['date'])
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->rules(['date'])
                            ->placeholder('End Date'),
                        FileUpload::make('document_url')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        Hidden::make('vendor_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_type')->label('Contract Type'),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date'),
                Tables\Columns\TextColumn::make('end_date')->label('End Date'),
                Tables\Columns\ImageColumn::make('document_url')->square()->disk('s3')->label('Document'),
                Tables\Columns\TextColumn::make('building.name')->label('Building'),
                Tables\Columns\TextColumn::make('service.name')->label('Service'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
