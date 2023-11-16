<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('contract_type')
                    ->required()
                    ->maxLength(255),
                Select::make('building_id')
                    ->rules(['exists:buildings,id'])
                    ->relationship('building', 'name')
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->placeholder('Building'),
                Select::make('service_id')
                    ->rules(['exists:services,id'])
                    ->relationship('service', 'name')
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->placeholder('Service'),
                DatePicker::make('start_date')
                    ->rules(['date'])
                    ->required()
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
