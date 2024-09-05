<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalNoticeResource\Pages;
use App\Filament\Resources\LegalNoticeResource\RelationManagers;
use App\Models\LegalNotice;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LegalNoticeResource extends Resource
{
    protected static ?string $model = LegalNotice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('legalNoticeId'),
                TextInput::make('building_id'),
                TextInput::make('flat_id'),
                TextInput::make('owner_association_id'),
                TextInput::make('mollakPropertyId'),
                TextInput::make('registrationDate'),
                TextInput::make('registrationNumber'),
                TextInput::make('invoiceNumber'),
                TextInput::make('invoicePeriod'),
                TextInput::make('previousBalance'),
                TextInput::make('invoiceAmount'),
                TextInput::make('approvedLegalAmount'),
                TextInput::make('legalNoticePDF'),
                TextInput::make('isRDCCaseStart'),
                TextInput::make('isRDCCaseEnd'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legalNoticeId'),
                TextColumn::make('building_id'),
                TextColumn::make('flat_id'),
                TextColumn::make('owner_association_id'),
                TextColumn::make('mollakPropertyId'),
                TextColumn::make('registrationDate'),
                TextColumn::make('registrationNumber'),
                TextColumn::make('invoiceNumber'),
                TextColumn::make('invoicePeriod'),
                TextColumn::make('previousBalance'),
                TextColumn::make('invoiceAmount'),
                TextColumn::make('approvedLegalAmount'),
                TextColumn::make('legalNoticePDF'),
                TextColumn::make('isRDCCaseStart'),
                TextColumn::make('isRDCCaseEnd'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListLegalNotices::route('/'),
            'create' => Pages\CreateLegalNotice::route('/create'),
            'view' => Pages\ViewLegalNotice::route('/{record}'),
            'edit' => Pages\EditLegalNotice::route('/{record}/edit'),
        ];
    }
}
