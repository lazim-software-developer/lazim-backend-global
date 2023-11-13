<?php

namespace App\Filament\Resources\Master\RoleResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
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
                        ->required()
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Last Name')
                        ->required()
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('email')
                        ->rules(['email'])
                        ->unique('users', 'email', fn (?Model $record) => $record)
                        ->email()
                        ->required()
                        ->default('NA')
                        ->placeholder('Email')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('phone')
                        ->rules(['max:10', 'string'])
                        ->unique('users', 'phone', fn (?Model $record) => $record)
                        ->placeholder('Phone')
                        ->required()
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->required()
                        ->default(0)
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
                Tables\Columns\TextColumn::make('first_name')
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('last_name')
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->default(0)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
