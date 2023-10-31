<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\UserResource\Pages;
use App\Models\User\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon       = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel      = 'Owner';
    protected static ?string $navigationGroup      = 'Flat Management';
    protected static bool $shouldRegisterNavigation = false;
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

                    TextInput::make('lazim_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->unique(
                            'users',
                            'lazim_id',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Lazim Id'),

                    Select::make('role_id')
                        ->rules(['exists:roles,id'])
                        ->required()
                        ->relationship('role', 'name')
                        ->searchable()
                        ->placeholder('Role'),
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
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('last_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('lazim_id')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('role.name')
                    ->toggleable()
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
            UserResource\RelationManagers\AttendancesRelationManager::class,
            UserResource\RelationManagers\BuildingPocsRelationManager::class,
            UserResource\RelationManagers\DocumentsRelationManager::class,
            UserResource\RelationManagers\ComplaintsRelationManager::class,
            UserResource\RelationManagers\FacilityBookingsRelationManager::class,
            UserResource\RelationManagers\FlatTenantsRelationManager::class,
            UserResource\RelationManagers\FlatVisitorsRelationManager::class,
            UserResource\RelationManagers\VendorsRelationManager::class,
            UserResource\RelationManagers\FlatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
