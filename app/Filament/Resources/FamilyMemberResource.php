<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyMemberResource\Pages;
use App\Filament\Resources\FamilyMemberResource\RelationManagers;
use App\Models\FamilyMember;
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

class FamilyMemberResource extends Resource
{
    protected static ?string $model = FamilyMember::class;
    protected static ?string $modelLabel      = 'Family Members';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')->required(),
                TextInput::make('last_name')->placeholder('NA'),
                TextInput::make('phone')->placeholder('NA'),
                TextInput::make('passport_number')->required(),
                TextInput::make('passport_expiry_date')->required(),
                TextInput::make('emirates_id')->required(),
                TextInput::make('emirates_expiry_date')->required(),
                TextInput::make('gender')->required(),
                TextInput::make('relation')->required(),
                Select::make('user_id')
                ->relationship('resident','first_name')->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultGroup('resident.first_name')
            ->columns([
                TextColumn::make('first_name'),
                TextColumn::make('gender'),
                TextColumn::make('relation'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListFamilyMembers::route('/'),
            // 'create' => Pages\CreateFamilyMember::route('/create'),
            'view' => Pages\ViewFamilyMember::route('/{record}'),
            // 'edit' => Pages\EditFamilyMember::route('/{record}/edit'),
        ];
    }
}
