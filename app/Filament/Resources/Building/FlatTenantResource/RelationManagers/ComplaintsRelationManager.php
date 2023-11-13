<?php

namespace App\Filament\Resources\Building\FlatTenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaints';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('complaintable_type')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Complaintable Type')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('complaintable_id')
                        ->rules(['max:255'])
                        ->placeholder('Complaintable Id')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('complaint_type')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Complaint Type')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('category')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Category')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('open_time')
                        ->rules(['date'])
                        ->placeholder('Open Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('close_time')
                        ->rules(['date'])
                        ->placeholder('Close Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    KeyValue::make('photo')->columnSpan([
                        'default' => 12,
                        'md' => 12,
                        'lg' => 12,
                    ]),

                    KeyValue::make('remarks')
                        ->required()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('status')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Status')
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
                Tables\Columns\TextColumn::make('complaintable_type')->limit(
                    50
                ),
                Tables\Columns\TextColumn::make('complaintable_id')->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')->limit(50),
                Tables\Columns\TextColumn::make('complaint_type')->limit(50),
                Tables\Columns\TextColumn::make('category')->limit(50),
                Tables\Columns\TextColumn::make('open_time')->dateTime(),
                Tables\Columns\TextColumn::make('close_time')->dateTime(),
                Tables\Columns\TextColumn::make('status')->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
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
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
