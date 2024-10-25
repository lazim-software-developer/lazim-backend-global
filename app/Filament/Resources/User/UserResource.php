<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\UserResource\Pages;
use App\Models\Master\Role;
use App\Models\User\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel       = 'Owner';
    protected static ?string $navigationGroup       = 'Flat Management';
    protected static ?string $modelLabel            = 'Users';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])
                ->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->required()->disabledOn('edit')
                        ->placeholder('First Name'),

                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->nullable()->disabledOn('edit')
                        ->placeholder('Last Name'),

                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                        ->required()->disabledOn('edit')
                        ->unique(
                            'users',
                            'email',
                            fn(?Model $record) => $record
                        )
                        ->email()
                        ->placeholder('Email'),

                    TextInput::make('phone')
                        ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                    // ->required()
                        ->disabledOn('edit')
                        ->prefix('971')
                        ->unique(
                            'users',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone'),

                    // TextInput::make('lazim_id')
                    //     ->rules(['max:50', 'string'])
                    //     ->required()
                    //     ->unique(
                    //         'users',
                    //         'lazim_id',
                    //         fn (?Model $record) => $record
                    //     )
                    //     ->placeholder('Lazim Id'),
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        ->disabledOn('edit')
                    // ->multiple()
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Role::where('name', 'Admin')->pluck('name', 'id');
                            } else {
                                $oaId = auth()->user()?->owner_association_id;
                                return Role::whereNotIn('name',
                                    ['Admin', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director',
                                        'Vendor', 'Property Manager', 'Facility Manager'])
                                    ->where('owner_association_id', $oaId)
                                    ->pluck('name', 'id');
                            }
                        })
                        ->preload()->required()
                        ->searchable(),
                    // Select::make('role_id')
                    // ->label('Role')
                    //     ->rules(['exists:roles,id'])
                    //     ->required()->disabledOn('edit')
                    //     ->options(function () {
                    //         $oaId = auth()->user()?->owner_association_id;
                    //         return Role::whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])
                    //             ->pluck('name', 'id');
                    //     })->searchable()->preload()
                    //     ->placeholder('Role'),
                    // Toggle::make('phone_verified')
                    //     ->rules(['boolean'])
                    //     ->hidden()
                    //     ->nullable(),
                    Toggle::make('active')
                    // ->rules(['boolean'])
                        ->default(true)
                        ->nullable(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $roles = Role::where('name', 'Admin')->pluck('id');
        } else {
            $roles = Role::whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor', 'Facility Manager'])->pluck('id');
        }
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', $roles)->where('id', '!=', auth()->user()->id))
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(15),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable()
                    ->limit(50)
                    ->default('--'),
                Tables\Columns\ToggleColumn::make('active')
                    ->toggleable(),
                // Tables\Columns\TextColumn::make('lazim_id')
                //     ->toggleable()
                //     ->searchable()
                //     ->limit(50),
                Tables\Columns\TextColumn::make('role.name')
                    ->toggleable()->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // UserResource\RelationManagers\AttendancesRelationManager::class,
            // UserResource\RelationManagers\VendorsRelationManager::class,

            // UserResource\RelationManagers\BuildingPocsRelationManager::class,
            // UserResource\RelationManagers\DocumentsRelationManager::class,
            // UserResource\RelationManagers\ComplaintsRelationManager::class,
            // UserResource\RelationManagers\FacilityBookingsRelationManager::class,
            // UserResource\RelationManagers\FlatTenantsRelationManager::class,
            // UserResource\RelationManagers\FlatVisitorsRelationManager::class,
            // UserResource\RelationManagers\FlatsRelationManager::class,
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
