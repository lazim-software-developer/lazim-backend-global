<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoveOutFormsDocumentResource\Pages;
use App\Filament\Resources\MoveOutFormsDocumentResource\RelationManagers;
use App\Models\Forms\MoveInOut;
use App\Models\MoveOutFormsDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MoveOutFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'MoveOut';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Forms Document';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->poll('60s')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'move-out')->withoutGlobalScopes())
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('email')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('phone')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('type')
            ->searchable()
            ->default('NA')
                ->limit(50),
            TextColumn::make('moving_date')
                ->toggleable()
                ->limit(50),
            TextColumn::make('moving_time')
                ->toggleable()
                ->limit(50),
            TextColumn::make('building.name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('user.first_name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('flat.property_number')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('handover_acceptance')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('receipt_charges')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('contract')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('title_deed')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('passport')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('dewa')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('cooling_registration')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('gas_registration')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('vehicle_registration')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('movers_license')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('movers_liability')
                ->searchable()
                ->default('NA')
                ->limit(50),
            IconColumn::make('approved')
                ->boolean(),
            
        ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoveOutFormsDocuments::route('/'),
            //'create' => Pages\CreateMoveOutFormsDocument::route('/create'),
            //'edit' => Pages\EditMoveOutFormsDocument::route('/{record}/edit'),
        ];
    }    
}
