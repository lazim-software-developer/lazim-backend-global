<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use DateTime;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->searchable()
                        ->placeholder('Building')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    DatePicker::make('date')
                        ->rules(['date'])
                        ->placeholder('Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    DateTimePicker::make('entry_time')
                        ->rules(['date_format:H:i:s'])
                        ->placeholder('Entry Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    TimePicker::make('exit_time')
                        ->rules(['date_format:H:i:s'])
                        ->placeholder('Exit Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    Toggle::make('attendance')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    Select::make('approved_by')
                        ->rules(['exists:users,id'])
                        ->relationship('userAttendanceApprove', 'first_name')
                        ->searchable()
                        ->placeholder('User Attendance Approve')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
    
                    DateTimePicker::make('approved_on')
                        ->rules(['date'])
                        ->placeholder('Approved On')
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
                Tables\Columns\TextColumn::make('building.name')->limit(50),
                Tables\Columns\TextColumn::make(
                    'userAttendance.first_name'
                )->limit(50),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('entry_time'),
                Tables\Columns\TextColumn::make('exit_time'),
                Tables\Columns\IconColumn::make('attendance'),
                Tables\Columns\TextColumn::make(
                    'userAttendanceApprove.first_name'
                )->limit(50),
                Tables\Columns\TextColumn::make('approved_on')->dateTime(),
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
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
