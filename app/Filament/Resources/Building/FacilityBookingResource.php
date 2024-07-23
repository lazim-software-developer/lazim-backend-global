<?php

namespace App\Filament\Resources\Building;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\EditAction;
use App\Models\Building\FacilityBooking;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Building\FacilityBookingResource\Pages;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FacilityBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Facility Bookings';
    protected static ?string $navigationGroup = 'Property Management';
    protected static ?string $modelLabel      = 'Facility Bookings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([

                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->options(function () {
                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return Building::all()->pluck('name', 'id');
                                }
                                else{
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                    ->pluck('name', 'id');
                                }    
                            })
                            ->reactive()
                            ->disabledOn('edit')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),

                        Select::make('bookable_id')
                            ->label('Facility ')
                            ->required()
                            ->disabledOn('edit')
                            ->options(
                                DB::table('facilities')
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->label('Facility'),

                        Hidden::make('bookable_type')
                            ->default('App\Models\Master\Facility'),

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('user', 'first_name')
                            ->options(function () {
                                $roleId = Role::whereIn('name',['tenant','owner'])->pluck('id')->toArray();

                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return User::whereIn('role_id', $roleId)->pluck('first_name', 'id'); 
                                }
                                else{
                                    return User::whereIn('role_id', $roleId)->where('owner_association_id',auth()->user()?->owner_association_id)->pluck('first_name', 'id');
                                }
                            })
                            ->preload()
                            ->disabledOn('edit')
                            ->searchable()
                            ->placeholder('User'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->disabledOn('edit')
                            ->minDate(now()->subYears(150))
                            ->closeOnDateSelection()
                            ->required()
                            ->placeholder('Date'),
                        TimePicker::make('start_time')
                            ->required()
                            ->disabledOn('edit')
                            ->minDate(now()->subYears(150))
                            ->placeholder('Start Time'),
                        TimePicker::make('end_time')
                            ->after('start_time')
                            ->disabledOn('edit')
                            ->required()
                            ->placeholder('End Time'),
                        // TextInput::make('remarks')
                        //     ->default('NA')
                        //     ->disabledOn('edit')
                        //     ->required(),
                        // TextInput::make('reference_number')
                        //     ->rules(['numeric'])
                        //     ->disabledOn('edit')
                        //     ->default('0')
                        //     ->numeric()
                        //     ->placeholder('References Number'),
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('bookable.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50)
                    ->label('Facility'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->default('NA')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('end_time')
                    ->default('NA')
                    ->time(),
                // Tables\Columns\TextColumn::make('reference_number')
                //     ->default('0')
                //     ->searchable(),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name',function (Builder $query){
                        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin')
                        {
                            $query->where('owner_association_id',Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn($record) => $record->approved === 0)
                    ->button()
                    ->form([
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ])
                    ->fillForm(fn(FacilityBooking $record): array => [
                        'approved' => $record->approved,
                    ])
                    ->action(function (FacilityBooking $record, array $data): void {
                        $record->approved = $data['approved'];
                        $record->save();
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
            'index' => Pages\ListFacilityBookings::route('/'),
            'create' => Pages\CreateFacilityBooking::route('/create'),
            // 'view' => Pages\ViewFacilityBooking::route('/{record}'),
            'edit' => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_building::facility::booking');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_building::facility::booking');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_building::facility::booking');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_building::facility::booking');
    }
}
