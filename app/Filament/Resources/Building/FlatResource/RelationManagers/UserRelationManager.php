<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('First Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Last Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('email')
                        ->rules(['email'])
                        ->unique('users', 'email', fn(?Model $record) => $record)
                        ->email()
                        ->placeholder('Email')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('phone')
                        ->rules(['max:10', 'string'])
                        ->unique('users', 'phone', fn(?Model $record) => $record)
                        ->placeholder('Phone')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    // TextInput::make('password')
                    //     ->password()
                    //     ->dehydrateStateUsing(fn($state) => \Hash::make($state))
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
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TextInput::make('lazim_id')
                        ->rules(['max:50', 'string'])
                        ->unique('users', 'lazim_id', fn(?Model $record) => $record)
                        ->placeholder('Lazim Id')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    Select::make('role_id')
                        ->rules(['exists:roles,id'])
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->limit(50),
                Tables\Columns\TextColumn::make('last_name')->limit(50),
                Tables\Columns\TextColumn::make('email')->limit(50),
                Tables\Columns\TextColumn::make('phone')->limit(50),
                Tables\Columns\IconColumn::make('phone_verified'),
                Tables\Columns\IconColumn::make('active'),
                Tables\Columns\TextColumn::make('lazim_id')->limit(50),
                Tables\Columns\TextColumn::make('role.name')->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
}
