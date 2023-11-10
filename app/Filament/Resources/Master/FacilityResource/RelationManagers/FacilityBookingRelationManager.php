<?php

namespace App\Filament\Resources\Master\FacilityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\FlareClient\Time\Time;

class FacilityBookingRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
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

                    DatePicker::make('date')
                        ->rules(['date'])
                        ->placeholder('Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TimePicker::make('start_time')
                        ->rules(['date_format:H:i:s'])
                        ->placeholder('Start Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TimePicker::make('end_time')
                        ->rules(['date_format:H:i:s'])
                        ->placeholder('End Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('order_id')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Order Id')
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('payment_status')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Payment Status')
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('remarks')
                        ->required()
                        ->default('NA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('reference_number')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Reference Number')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('approved')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('approved_by')
                        ->label('Approved by')
                        ->rules(['exists:users,id'])
                        ->relationship('userFacilityBookingApprove', 'first_name')
                        ->searchable()
                        ->default('NA')
                        ->placeholder('Approved by')
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
                Tables\Columns\TextColumn::make('bookable.name')->limit(50)->label('Facility'),
                Tables\Columns\TextColumn::make('user.first_name')->limit(50)->default('NA'),
                Tables\Columns\TextColumn::make('date')->date()->default('NA'),
                Tables\Columns\TextColumn::make('start_time')->default('NA'),
                Tables\Columns\TextColumn::make('end_time')->default('NA'),
                Tables\Columns\TextColumn::make('order_id')->limit(50)->default('NA'),
                Tables\Columns\TextColumn::make('payment_status')->limit(50)->default('NA'),
                Tables\Columns\TextColumn::make('reference_number')->limit(50)->default('NA'),
                Tables\Columns\IconColumn::make('approved')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make(
                    'userFacilityBookingApprove.first_name'
                )->limit(50)->label('Approved By')->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
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
