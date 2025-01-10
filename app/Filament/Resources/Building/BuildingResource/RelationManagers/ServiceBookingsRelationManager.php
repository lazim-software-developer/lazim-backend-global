<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\FacilityBooking;
use App\Models\ExpoPushNotification;
use App\Models\Master\Role;
use App\Models\Master\Service;
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

class ServiceBookingsRelationManager extends RelationManager
{
    use UtilsTrait;
    protected static string $relationship = 'facilityBookings';

    protected static ?string $title      = 'Personal Service Bookings';
    protected static ?string $modelLabel = 'Personal Service Booking';

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
                            ->options(
                                DB::table('services')
                                    ->where('type', 'inhouse')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->label('Service')
                            ->preload()
                            ->disabledOn('edit')
                            ->label('Service'),

                        Hidden::make('bookable_type')
                            ->default('App\Models\Master\Service'),

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
                            ->preload()
                            ->disabledOn('edit')
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
                        // TimePicker::make('end_time')
                        //     ->required()
                        //     ->disabledOn('edit')
                        //     ->placeholder('End Time'),
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required(),
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('bookable_type', 'App\Models\Master\Service')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('bookable.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Services'),
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
                // TextColumn::make('end_time')
                //     ->searchable()
                //     ->default('NA')
                //     ->label('End Time'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Create Personal Service Booking'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Model $record) {
                        $user = FacilityBooking::where('id', $record->id)->first();
                        if ($user->bookable_type == 'App\Models\Master\Service') {
                            $serviceName = Service::where('id', $user->bookable_id)->first();
                            if ($user->approved != null) {
                                if ($user->approved == 1) {
                                    $expoPushTokens = ExpoPushNotification::where('user_id', $user->user_id)->pluck('token');
                                    if ($expoPushTokens->count() > 0) {
                                        foreach ($expoPushTokens as $expoPushToken) {
                                            $message = [
                                                'to'    => $expoPushToken,
                                                'sound' => 'default',
                                                'title' => $serviceName->name . ' Booking Status.',
                                                'body'  => 'Your personal service booking request for ' . $serviceName->name . ' is approved',
                                                'data'  => ['notificationType' => 'MyBookingsService'],
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
                                            'body'      => 'Your personal service booking request for ' . $serviceName->name . ' is approved',
                                            'duration'  => 'persistent',
                                            'icon'      => 'heroicon-o-document-text',
                                            'iconColor' => 'warning',
                                            'title'     => 'Personal Service Booking Status.',
                                            'view'      => 'notifications::notification',
                                            'viewData'  => [],
                                            'format'    => 'filament',
                                            'url'       => 'MyBookingsService',
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
                                                'title' => $serviceName->name . ' Booking Status.',
                                                'body'  => 'Your personal service booking request for ' . $serviceName->name . ' is rejected',
                                                'data'  => ['notificationType' => 'MyBookingsService'],
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
                                            'body'      => 'Your personal service booking request for ' . $serviceName->name . ' is rejected',
                                            'duration'  => 'persistent',
                                            'icon'      => 'heroicon-o-document-text',
                                            'iconColor' => 'danger',
                                            'title'     => $serviceName->name . ' Booking Status.',
                                            'view'      => 'notifications::notification',
                                            'viewData'  => [],
                                            'format'    => 'filament',
                                            'url'       => 'MyBookingsService',
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

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Personal Service Bookings';
    }
}
