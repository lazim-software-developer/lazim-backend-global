<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\Master\Facility;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class FacilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'facilities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->limit(50),
            Tables\Columns\TextColumn::make('icon')->limit(50),
            Tables\Columns\IconColumn::make('active')
            ->boolean()
            ->trueIcon('heroicon-o-check-badge')
            ->falseIcon('heroicon-o-x-mark'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(fn () => Select::make('recordId')
                            ->label('Facility')
                            ->relationship('buildings', 'facility_id')
                            ->options(Facility::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->preload()
                        )
            ]);
    }
}
