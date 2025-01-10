<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\FacilityBooking;
use App\Models\ExpoPushNotification;
use App\Models\Master\Facility;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Traits\UtilsTrait;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FacilityBookingsRelationManager extends RelationManager
{
    use UtilsTrait;
    protected static string $relationship = 'facilityBookings';

    protected static ?string $title      = 'Amenity Bookings';
    protected static ?string $modelLabel = 'Amenity Booking';

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
                            ->required()
                            ->options(function (RelationManager $livewire) {

                                $facilityId = DB::table('building_facility')->where('building_id', $livewire->ownerRecord->id)->pluck('facility_id');
                                return DB::table('facilities')
                                    ->whereIn('id', $facilityId)
                                    ->where('active', true)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->label('Amenities')
                            ->disabledOn('edit')
                            ->preload(),

                        Hidden::make('bookable_type')
                            ->default('App\Models\Master\Facility'),

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                $roleId = Role::whereIn('name', ['tenant', 'owner'])->pluck('id')->toArray();

                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return User::whereIn('role_id', $roleId)->pluck('first_name', 'id');
                                } else {
                                    return User::whereIn('role_id', $roleId)->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('first_name', 'id');
                                }
                            })
                            ->searchable()
                            ->disabledOn('edit')
                            ->preload()
                            ->placeholder('User'),

                        Hidden::make('owner_association_id')
                            ->default(auth()->user()?->owner_association_id),

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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('bookable_type', 'App\Models\Master\Facility')->withoutGlobalScopes())
            // ->recordTitleAttribute('building_id')
            ->columns([
                TextColumn::make('bookable.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Amenity'),
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
                    ->after(function (Model $record) {
                        $user = FacilityBooking::where('id', $record->id)->first();
                        if ($user->bookable_type == 'App\Models\Master\Facility') {
                            $facilityName = Facility::where('id', $user->bookable_id)->first();
                            if ($user->approved != null) {
                                if ($user->approved == 1) {
                                    $expoPushTokens = ExpoPushNotification::where('user_id', $user->user_id)->pluck('token');
                                    if ($expoPushTokens->count() > 0) {
                                        foreach ($expoPushTokens as $expoPushToken) {
                                            $message = [
                                                'to'    => $expoPushToken,
                                                'sound' => 'default',
                                                'title' => $facilityName->name . ' Booking Status.',
                                                'body'  => 'Your amenity booking request for ' . $facilityName->name . ' is approved',
                                                'data'  => ['notificationType' => 'MyBookingsFacility'],
                                            ];
                                            $this->expoNotification($message);
                                        }
                                    }
                                    DB::table('notifications')->insert([
                                        'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                        'type'            => 'Filament\Notifications\DatabaseNotification',
                                        'notifiable_type' => 'App\Models\User\User',
                                        'notifiable_id'   => $user->user_id,
                                        'data'            => json_encode([
                                            'actions'   => [],
                                            'body'      => 'Your amenity booking request for ' . $facilityName->name . ' is approved',
                                            'duration'  => 'persistent',
                                            'icon'      => 'heroicon-o-document-text',
                                            'iconColor' => 'warning',
                                            'title'     => $facilityName->name . ' Booking Status.',
                                            'view'      => 'notifications::notification',
                                            'viewData'  => [],
                                            'format'    => 'filament',
                                            'url'       => 'MyBookingsFacility',
                                        ]),
                                        'created_at'      => now()->format('Y-m-d H:i:s'),
                                        'updated_at'      => now()->format('Y-m-d H:i:s'),
                                    ]);
                                }

                                if ($user->approved == 0) {
                                    $expoPushTokens = ExpoPushNotification::where('user_id', $user->user_id)->pluck('token');
                                    if ($expoPushTokens->count() > 0) {
                                        foreach ($expoPushTokens as $expoPushToken) {
                                            $message = [
                                                'to'    => $expoPushToken,
                                                'sound' => 'default',
                                                'title' => $facilityName->name . ' Booking Status.',
                                                'body'  => 'Your amenity booking request for ' . $facilityName->name . ' is rejected',
                                                'data'  => ['notificationType' => 'MyBookingsFacility'],
                                            ];
                                            $this->expoNotification($message);
                                        }
                                    }
                                    DB::table('notifications')->insert([
                                        'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                                        'type'            => 'Filament\Notifications\DatabaseNotification',
                                        'notifiable_type' => 'App\Models\User\User',
                                        'notifiable_id'   => $user->user_id,
                                        'data'            => json_encode([
                                            'actions'   => [],
                                            'body'      => 'Your amenity booking request for ' . $facilityName->name . ' is rejected',
                                            'duration'  => 'persistent',
                                            'icon'      => 'heroicon-o-document-text',
                                            'iconColor' => 'danger',
                                            'title'     => $facilityName->name . ' Booking Status.',
                                            'view'      => 'notifications::notification',
                                            'viewData'  => [],
                                            'format'    => 'filament',
                                            'url'       => 'MyBookingsFacility',
                                        ]),
                                        'created_at'      => now()->format('Y-m-d H:i:s'),
                                        'updated_at'      => now()->format('Y-m-d H:i:s'),
                                    ]);
                                }
                            }
                        }
                    }),
            ]);
    }
}
