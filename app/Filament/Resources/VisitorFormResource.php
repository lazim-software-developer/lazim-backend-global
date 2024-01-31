<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorFormResource\Pages;
use App\Filament\Resources\VisitorFormResource\RelationManagers;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitorFormResource extends Resource
{
    protected static ?string $model = FlatVisitor::class;
    protected static ?string $title = 'Flat visitor';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Visitors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('flat_id')->label('Unit')
                ->relationship('flat', 'property_number')->disabled(),
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('start_time')->label('Date')->disabled(),
                TextInput::make('time_of_viewing')->label('Time')->disabled(),
                TextInput::make('number_of_visitors')->disabled(),
                Select::make('status')
                                ->options([
                                    'approved' => 'Approve',
                                    'rejected' => 'Reject',
                                ])
                                ->disabled(function(FlatVisitor $record){
                                    return $record->status != null;
                                })
                                ->required()
                                ->searchable()
                                ->live(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flat.property_number')
                ->label('Unit'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('start_time')
                    ->label('Date')
                    // ->date()
                    ->default('NA'),
                TextColumn::make('time_of_viewing')
                    ->label('time')
                    // ->time()
                    ->default('NA'),
                TextColumn::make('number_of_visitors')->default('NA'),
                TextColumn::make('status')->default('NA'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListVisitorForms::route('/'),
            // 'create' => Pages\CreateVisitorForm::route('/create'),
            'edit' => Pages\EditVisitorForm::route('/{record}/edit'),
        ];
    }
}
