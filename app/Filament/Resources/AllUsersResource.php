<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllUsersResource\Pages;
use App\Filament\Resources\AllUsersResource\RelationManagers;
use App\Models\User\User;
use App\Models\UserApproval;
use Filament\Forms;
use App\Models\Master\Role;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AllUsersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel  = 'All Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $roles = Role::where('name', 'Admin')->pluck('id');
        } else {
            $roles = Role::whereIn('name', ['Vendor', 'Tenant', 'Owner'])->pluck('id');
        }
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('owner_association_id', operator: auth()->user()?->owner_association_id)->whereIn('role_id', $roles)->where('id', '!=', auth()->user()->id))

            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('First Name')
                    ->toggleable()
                    ->searchable()
                    ->limit(15),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->toggleable()
                    ->searchable()
                    ->limit(50)
                    ->default('NA'),
                Tables\Columns\ToggleColumn::make('active')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Filter by Role')
                    ->searchable()
                    ->options([
                        1 => 'Owner',
                        2 => 'Vendor',
                        3 => ''

                    ])
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAllUsers::route('/'),
            // 'create' => Pages\CreateAllUsers::route('/create'),
            // 'edit' => Pages\EditAllUsers::route('/{record}/edit'),
        ];
    }
}
