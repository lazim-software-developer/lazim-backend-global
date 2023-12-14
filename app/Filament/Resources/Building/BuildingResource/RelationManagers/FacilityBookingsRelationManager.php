<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use App\Models\Building\FacilityBooking;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class FacilityBookingsRelationManager extends RelationManager
{
    use UtilsTrait;
    protected static string $relationship = 'facilityBookings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),

                        Select::make('bookable_id')
                            ->options(
                                DB::table('facilities')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->label('Facility')
                            ->disabledOn('edit')
                            ->preload()
                            ->label('Facility'),

                        Hidden::make('bookable_type')
                            ->default('App\Models\Master\Facility'),

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                return User::whereIn('role_id', [1,11])->pluck('first_name', 'id');
                            })
                            ->searchable()
                            ->disabledOn('edit')
                            ->preload()
                            ->placeholder('User'),

                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),

                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Date'),
                        TimePicker::make('start_time')
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Start Time'),
                        TimePicker::make('end_time')
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('End Time'),
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required(),
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('bookable_type', 'App\Models\Master\Facility')->withoutGlobalScopes())
            ->recordTitleAttribute('building_id')
            ->columns([
                TextColumn::make('bookable.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Facility'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->label('User'),
                TextColumn::make('date')
                    ->date()
                    ->searchable()
                    ->default('NA')
                    ->label('Date'),
                TextColumn::make('start_time')
                    ->searchable()
                    ->default('NA')
                    ->label('Start Time'),
                TextColumn::make('end_time')
                    ->searchable()
                    ->default('NA')
                    ->label('End Time'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->after(function (RelationManager $livewire) {
                    $user = FacilityBooking::where('id',$livewire->ownerRecord->id)->first();
                    if ($user->approved == 1) {
                        $expoPushTokens = ExpoPushNotification::where('user_id', $user->user_id)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'title' => 'Facility Booking Updated!',
                                    'body' => auth()->user()->first_name . ' approved your Facility Booking form.',
                                    'data' => ['notificationType' => 'app_notification'],
                                ];
                                $this->expoNotification($message);
                            }
                        }
                    }

                    if ($user->approved == 0) {
                        $expoPushTokens = ExpoPushNotification::where('user_id', $user->user_id)->pluck('token');
                        if ($expoPushTokens->count() > 0) {
                            foreach ($expoPushTokens as $expoPushToken) {
                                $message = [
                                    'to' => $expoPushToken,
                                    'sound' => 'default',
                                    'title' => 'Facility Booking Updated!',
                                    'body' => auth()->user()->first_name . ' rejected your Facility Booking form.',
                                    'data' => ['notificationType' => 'app_notification'],
                                ];
                                $this->expoNotification($message);
                            }
                        }
                    }
                }),
            ]);
    }
}
