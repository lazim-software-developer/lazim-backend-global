<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class FlatsRelationManager extends RelationManager
{
    protected static string $relationship = 'flats';
    protected static ?string $modelLabel  = 'Units';

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
                        ->regex('/^[\w\-\s]+$/')
                        ->rules(['numeric'])
                        ->numeric()
                        ->label('Unit Number')
                        ->columnSpan([
                            'default' => 12,
                            'md'      => 12,
                            'lg'      => 12,
                        ]),
                    TextInput::make('description')
                        ->placeholder('Description')
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md'      => 12,
                            'lg'      => 12,
                        ]),
                    Hidden::make('building_id')
                        ->default(function (RelationManager $livewire) {
                            return $livewire->ownerRecord->id;
                        }),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
                TextColumn::make('property_number')
                    ->default('NA')
                    ->searchable()
                    ->label('Unit Number'),
                TextColumn::make('suit_area')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('applicable_area')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('virtual_account_number')
                    ->default('NA')
                    ->searchable()
                    ->hidden(in_array(auth()->user()->role->name, ['Property Manager', 'Admin']))
                    ->limit(50),
                TextColumn::make('parking_count')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                // ->form([
                //     TextInput::make('property_number')
                //         ->rules(['numeric'])
                //         ->numeric()
                //         ->placeholder('Property Number')
                //         ->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //         ]),
                //     TextInput::make('description')
                //         ->placeholder('Description')
                //         ->default('NA')
                //         ->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //         ]),
                //         Select::make('building_id')
                //             ->rules(['exists:buildings,id'])
                //             ->relationship('building', 'name')
                //             ->reactive()
                //             ->default('NA')
                //             ->preload()
                //             ->searchable()
                //             ->placeholder('Building')
                //             ->columnSpan([
                //                 'default' => 12,
                //                 'md' => 12,
                //                 'lg' => 12,
                //             ]),
                //         TextInput::make('suit_area')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('actual_area')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('balcony_area')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('applicable_area')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('virtual_account_number')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('parking_count')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                //         TextInput::make('plot_number')
                //             ->placeholder('NA')->columnSpan([
                //             'default' => 12,
                //             'md' => 12,
                //             'lg' => 12,
                //             ]),
                // ])
                // ->fillForm(fn (Flat $record): array => [
                //     'property_number' => $record->property_number,
                // ]),
                Action::make('custom_edit')
                    ->label('Open')
                    ->url(function ($record) {
                        if (class_exists(\App\Filament\Resources\Building\FlatResource::class)) {
                            return \App\Filament\Resources\Building\FlatResource::getUrl('edit', ['record' => $record->id]);
                        }
                        return route('filament.pages.view-floor', ['floorId' => $record->id]);
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}
