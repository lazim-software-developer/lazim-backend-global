<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\OwnerResource\Pages;
use App\Filament\Resources\User\OwnerResource\RelationManagers;
use App\Filament\Resources\User\OwnerResource\RelationManagers\UserDocumentsRelationManager;
use App\Models\User\Owner;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel      = 'Owner';
    protected static ?string $navigationGroup      = 'User Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2])
                ->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('First Name'),
                    Hidden::make('owner_association_id')
                        ->default(auth()->user()->owner_association_id),
                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->nullable()
                        ->placeholder('Last Name'),

                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                        ->required()
                        ->unique(
                            'users',
                            'email',
                            fn(?Model $record) => $record
                        )
                        ->email()
                        ->placeholder('Email'),

                    TextInput::make('phone')
                        ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        ->required()
                        ->unique(
                            'users',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone'),

                    Hidden::make('role_id')
                        ->default(1),
                    Toggle::make('phone_verified')
                        ->rules(['boolean'])
                        ->hidden()
                        ->nullable(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('last_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
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
            UserDocumentsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            'create' => Pages\CreateOwner::route('/create'),
            'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }    
}
