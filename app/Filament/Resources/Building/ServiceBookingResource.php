<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\ServiceBookingResource\Pages;
use App\Filament\Resources\Building\ServiceBookingResource\RelationManagers;
use App\Models\Building\FacilityBooking;
use App\Models\Building\ServiceBooking;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Service Bookings';
    protected static ?string $navigationGroup = 'Property Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([

                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),

                    Select::make('bookable_id')
                        ->options(
                            DB::table('services')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                    ->searchable()
                    ->preload()
                    ->label('Service')
                    ->required()
                    ->placeholder('Service'),

                    Hidden::make('bookable_type')
                        ->default('App\Models\Master\Service'),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->preload()
                        // ->label('Approved By')
                        ->placeholder('User'),
                    DatePicker::make('date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Date'),
                    TimePicker::make('start_time')
                        ->required()
                        ->placeholder('Start Time'),
                    TimePicker::make('end_time')
                        ->default('NA')
                        ->placeholder('End Time'),
                    TextInput::make('remarks')
                    ->default('NA')
                        ->required(),
                    TextInput::make('reference_number')
                        ->rules(['numeric'])
                        ->default('0')
                        ->required()
                        ->numeric()
                        ->placeholder('References Number'),
                    // Toggle::make('approved')
                    //     ->rules(['boolean'])
                    //     ->required(),
                ]),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('building.name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            Tables\Columns\TextColumn::make('bookable.name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            Tables\Columns\TextColumn::make('user.first_name')
                ->default('NA')
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('date')
                ->searchable()
                ->default('NA')
                ->date(),
            Tables\Columns\TextColumn::make('start_time')
                ->searchable()
                ->default('NA')
                ->time(),
            Tables\Columns\TextColumn::make('reference_number')
                ->default('NA')
                ->searchable(),
            Tables\Columns\IconColumn::make('approved')
                ->boolean(),
        ])
        ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Action::make('Update Status')
                    ->button()
                    ->form([
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ])
                    ->fillForm(fn (FacilityBooking $record): array => [
                        'approved' => $record->status,
                    ])
                    ->action(function (FacilityBooking $record, array $data): void {
                        $record->approved = $data['approved'];
                        $record->save();
                    })
                    ->slideOver()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceBookings::route('/'),
            'create' => Pages\CreateServiceBooking::route('/create'),
            'view' => Pages\ViewServiceBooking::route('/{record}'),
            // 'edit' => Pages\EditServiceBooking::route('/{record}/edit'),
        ];
    }
}
