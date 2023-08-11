<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FacilityBookingResource\Pages;
use App\Filament\Resources\Building\FacilityBookingResource\RelationManagers;
use App\Models\Building\FacilityBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacilityBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?string $navigationGroup = 'Building Management';

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
            ->columns([
                Tables\Columns\TextColumn::make('facilities.name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable(isIndividual: false, isGlobal: true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->toggleable()
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->toggleable()
                    ->date(),
                Tables\Columns\TextColumn::make('end_time')
                    ->toggleable()
                    ->date(),
                Tables\Columns\TextColumn::make('order_id')
                    ->toggleable()
                    //->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('payment_status')
                    ->toggleable()
                    //->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('references_number')
                    ->toggleable(),
                    //->searchable(true, null, true),
                Tables\Columns\IconColumn::make('approved')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->toggleable()
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilityBookings::route('/'),
            'create' => Pages\CreateFacilityBooking::route('/create'),
            'edit' => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }    
}
