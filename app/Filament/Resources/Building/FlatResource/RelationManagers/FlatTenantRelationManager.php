<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FlatTenantRelationManager extends RelationManager
{
    protected static string $relationship = 'tenants';

    protected static ?string $title = 'Resident History';

    protected static ?string $pluralModelLabel = 'Resident History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('tenant_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('primary')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('start_date')
                        ->rules(['date'])
                        ->placeholder('Start Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('end_date')
                        ->rules(['date'])
                        ->placeholder('End Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
            ->columns([
                // Tables\Columns\TextColumn::make('flat.description')->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')->limit(50),
                // Tables\Columns\IconColumn::make('primary'),
                Tables\Columns\TextColumn::make('start_date')->date()
                    ->formatStateUsing(fn(?string $state) => $state ? $state : 'NA'),
                Tables\Columns\TextColumn::make('end_date')->date()
                    ->formatStateUsing(fn(?string $state) => $state ? $state : 'NA'),
                Tables\Columns\TextColumn::make('role')->label('Type'),
                Tables\Columns\TextColumn::make('active')->label('Contract status')
                    ->formatStateUsing(fn(string $state) => $state ? 'On going' : 'Ended')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                        '' => 'danger',
                    }),
            ])
            ->defaultSort('active', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
