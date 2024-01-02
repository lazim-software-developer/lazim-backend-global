<?php

namespace App\Filament\Resources\Vendor;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Vendor\Attendance;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Vendor\AttendanceResource\Pages;
use App\Filament\Resources\Vendor\AttendanceResource\RelationManagers;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Vendor Management';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('userAttendance', 'first_name')
                            ->searchable()
                            ->placeholder('User Attendance'),

                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Date'),

                        TimePicker::make('entry_time')
                            ->rules(['date_format:H:i:s'])
                            ->nullable()
                            ->placeholder('Entry Time'),

                        TimePicker::make('exit_time')
                            ->rules(['date_format:H:i:s'])
                            ->nullable()
                            ->placeholder('Exit Time'),

                        Select::make('approved_by')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('userAttendanceApprove', 'first_name')
                            ->searchable()
                            ->placeholder('User Attendance Approve'),

                        Hidden::make('attendance')
                            ->default(0),
                        DateTimePicker::make('approved_on')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Approved On'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->poll('60s')
        ->columns([

            Tables\Columns\TextColumn::make('userAttendance.first_name')
                ->toggleable()
                ->limit(50),
            Tables\Columns\TextColumn::make('date')
                ->toggleable()
                ->date(),
            Tables\Columns\TextColumn::make('entry_time')
                ->toggleable()
                ->searchable(),
            Tables\Columns\TextColumn::make('exit_time')
                ->toggleable()
                ->searchable(),
            Tables\Columns\IconColumn::make('attendance')
                ->toggleable()
                ->boolean(),
            Tables\Columns\TextColumn::make(
                'userAttendanceApprove.first_name'
            )
                ->toggleable()
                ->limit(50),
            Tables\Columns\TextColumn::make('approved_on')
                ->toggleable()
                ->dateTime(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
