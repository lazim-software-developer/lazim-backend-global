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
use Filament\Tables\Columns\ImageColumn;
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
            ImageColumn::make('handover_acceptance')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('receipt_charges')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('contract')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('title_deed')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('passport')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('dewa')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('cooling_registration')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('gas_registration')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('vehicle_registration')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('movers_license')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
            ImageColumn::make('movers_liability')
                ->circular()
                ->disk('s3')
                ->directory('dev'),
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
