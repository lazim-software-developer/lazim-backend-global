<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Building\Flat;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class FlatsRelationManager extends RelationManager
{
    protected static string $relationship = 'flats';
    protected static ?string $modelLabel = 'Units';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Units';
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('property_number')
                        ->rules(['numeric'])
                        ->numeric()
                        ->label('Unit Number')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    TextInput::make('description')
                        ->placeholder('Description')
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    Hidden::make('building_id')
                        ->default(function(RelationManager $livewire){
                            return $livewire->ownerRecord->id;
                        }),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property_number'),
                Tables\Columns\TextColumn::make('building.name')->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->form([
                    TextInput::make('property_number')
                        ->rules(['numeric'])
                        ->numeric()
                        ->placeholder('Property Number')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    TextInput::make('description')
                        ->placeholder('Description')
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->default('NA')
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                ])
                ->fillForm(fn (Flat $record): array => [
                    'property_number' => $record->property_number,
                ]),
            ]);
    }
}
