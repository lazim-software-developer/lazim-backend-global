<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaints';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('building.name')
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('user.first_name')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('category')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('complaint')
                ->toggleable()
                ->default('NA')
                ->searchable(),
            TextColumn::make('status')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
        ])
            ->filters([
                //
            ]);
    }
}
