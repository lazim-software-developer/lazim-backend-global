<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationResource\Pages;
use App\Models\OwnerAssociation;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OwnerAssociationResource extends Resource
{
    protected static ?string $model                 = OwnerAssociation::class;
    protected static ?string $modelLabel            = 'Owner Association';
    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->required()
                        ->placeholder('User'),
                    TextInput::make('mollak_id')->label('Oa Number')
                        ->required()
                        ->disabled()

                        ->placeholder('OA Number'),
                    TextInput::make('trn_number')->label('TRN Number')
                        ->required()
                        ->disabled()

                        ->placeholder('TRN Number'),
                    TextInput::make('phone')
                        ->rules(['max:20', 'string'])
                        ->required()
                        ->placeholder('Contact Number')
                        ->unique(
                            'users',
                            'phone',
                            fn(?Model $record) => $record
                        ),
                    TextInput::make('address')
                        ->required()
                        ->placeholder('Address'),
                    TextInput::make('email')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->disabled(function () {
                            return DB::table('owner_associations')
                                ->where('verified', 1)
                                ->exists();
                        })
                        ->placeholder('Email')
                        ->unique(
                            'users',
                            'email',
                            fn(?Model $record) => $record
                        ),
                    Toggle::make('verified')
                        ->rules(['boolean'])
                        ->disabled(function () {
                            return DB::table('owner_associations')
                                ->where('verified', 1)
                                ->exists();
                        }),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('mollak_id')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('trn_number')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(),
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
            'index'  => Pages\ListOwnerAssociations::route('/'),
            'create' => Pages\CreateOwnerAssociation::route('/create'),
            'edit'   => Pages\EditOwnerAssociation::route('/{record}/edit'),
        ];
    }
}
