<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\AccountsManager;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AccountsManagerResource\Pages;
use App\Filament\Resources\AccountsManagerResource\RelationManagers;
use Filament\Forms\Components\Hidden;

class AccountsManagerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Accounts Manager';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'regex:/^[a-zA-Z\s]*$/',])
                        ->required(),
                    TextInput::make('last_name')
                        ->rules(['max:50', 'regex:/^[a-zA-Z\s]*$/',])
                        ->label('Last Name'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                        ->unique(
                            'users',
                            'email',
                            fn (?Model $record) => $record
                        )
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/', function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (DB::table('users')->where('phone', '971'.$value)->count() > 0) {
                                    $fail('The phone is already taken by a User.');
                                }
                            };
                        },])
                        ->prefix('971')
                        ->required()
                        ->maxLength(255),
                    FileUpload::make('profile_photo')
                        ->disk('s3')
                        ->directory('dev')
                        ->image()
                        ->label('Profile Photo'),
                    // Toggle::make('active')
                    //     ->rules(['boolean'])
                    //     ->default(true),
                    Hidden::make('active')
                        ->default(true)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListAccountsManagers::route('/'),
            'create' => Pages\CreateAccountsManager::route('/create'),
            'view' => Pages\ViewAccountsManagers::route('/{record}'),
            'edit' => Pages\EditAccountsManager::route('/{record}/edit'),
        ];
    }    
}
