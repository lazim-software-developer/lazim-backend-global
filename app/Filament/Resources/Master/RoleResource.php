<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\RoleResource\Pages;
use App\Models\Master\Role;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->rules(['max:50', 'regex:/^[a-zA-Z\s]*$/'])
                            ->required()
                            ->placeholder('Name'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $roles     = Role::wherenotNuLL('name');

        return $table
            ->query($roles)
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable()
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
            // RoleResource\RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
