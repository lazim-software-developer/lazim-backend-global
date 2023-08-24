<?php

namespace App\Filament\Resources\User;
use App\Filament\Resources\User\UserResource\Pages;
use App\Filament\Resources\User\UserResource\RelationManagers;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $recordTitleAttribute = 'first_name';
    protected static ?string $navigationLabel ='Owner';
    protected static ?string $navigationGroup = 'Flat Management';


    public static function form(Form $form): Form
    {
        return $form->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('First Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->nullable()
                        ->placeholder('Last Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('email')
                        ->rules(['email'])
                        ->required()
                        ->unique(
                            'users',
                            'email',
                            fn(?Model $record) => $record
                        )
                        ->email()
                        ->placeholder('Email')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('phone')
                        ->rules(['max:10', 'string'])
                        ->required()
                        ->unique(
                            'users',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    // TextInput::make('password')
                    //     ->required()
                    //     ->password()
                    //     ->dehydrateStateUsing(fn($state) =>Hash::make($state))
                    //     ->required(
                    //         fn(Component $livewire) => $livewire instanceof
                    //             Pages\CreateUser
                    //     )
                    //     ->placeholder('Password')
                    //     ->columnSpan([
                    //         'default' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //     ]),

                    Toggle::make('phone_verified')
                        ->rules(['boolean'])
                        ->nullable()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->nullable()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('lazim_id')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->unique(
                            'users',
                            'lazim_id',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Lazim Id')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('role_id')
                        ->rules(['exists:roles,id'])
                        ->required()
                        ->relationship('role', 'name')
                        ->searchable()
                        ->placeholder('Role')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
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
                Tables\Columns\IconColumn::make('phone_verified')
                    ->toggleable()
                    ->boolean(),
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
            ]);
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            // ->emptyStateActions([
            //     Tables\Actions\CreateAction::make(),
            // ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
