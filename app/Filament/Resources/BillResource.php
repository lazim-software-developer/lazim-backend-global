<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Carbon\Carbon;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Select::make('type')
                    ->preload()
                    ->searchable()
                    ->placeholder('Select the type of Bill')
                    ->options([
                        'BTU'               => 'BTU',
                        'DEWA'              => 'DEWA',
                        'Telecommunication' => 'DU/Etisalat',
                        'lpg'               => 'LPG',
                    ])
                    ->live()
                    ->reactive()
                    ->required(),

                Select::make('building_id')
                    ->label('Building')
                    ->afterStateUpdated(fn(Set $set) => $set('flat_id', null))
                    ->options(function () {
                        $role        = auth()->user()->role->name;
                        $buildingIds = DB::table('building_owner_association')
                            ->where('owner_association_id', auth()->user()->owner_association_id)
                            ->where('active', true)
                            ->pluck('building_id');

                        if (in_array($role, ['Property Manager', 'OA'])) {
                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');
                        } else {
                            return Building::pluck('name', 'id');
                        }
                    })
                    ->placeholder('Select the Building')
                    ->preload()
                    ->live()
                    ->searchable()
                    ->required(),

                Select::make('flat_id')
                    ->relationship('flat', 'property_number')
                    ->preload()
                    ->noSearchResultsMessage('No Flats found for this building.')
                    ->placeholder('Select the Flat')
                    ->options(function (callable $get) {
                        $role    = auth()->user()->role->name;
                        $pmFlats = DB::table('property_manager_flats')
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->where('active', true)
                            ->pluck('flat_id')
                            ->toArray();

                        if ($role == 'Property Manager') {
                            return Flat::where('building_id', $get('building_id'))
                                ->whereIn('id', $pmFlats)
                                ->pluck('property_number', 'id');
                        }
                        return Flat::where('building_id', $get('building_id'))
                            ->pluck('property_number', 'id');
                    })
                    ->disabled(function (callable $get) {
                        if ($get('building_id') == null) {
                            return true;
                        }
                        return false;
                    })
                    ->helperText(function (callable $get) {
                        if ($get('building_id') == null) {
                            return 'Select the Building to load it\'s flats';
                        }return '';
                    })
                    ->searchable()
                    ->required(),

                TextInput::make('bill_number')
                    ->label(function ($get) {
                        if($get('type') == 'BTU') {
                            return 'BTU/AC Number';
                        } elseif($get('type') == 'DEWA') {
                            return 'DEWA Number';
                        } elseif($get('type') == 'Telecommunication') {
                            return 'DU/Etisalat Number';
                        } elseif($get('type') == 'lpg') {
                            return 'LPG Number';
                        } else {
                            return 'Bill Number';
                        }
                    })
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('This will be automatically populated based on flat and bill type')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record || !$record->flat) return '';

                        if($record->type == 'BTU') {
                            return $record->flat->getAttribute('btu/ac_number');
                        } elseif($record->type == 'DEWA') {
                            return $record->flat->dewa_number;
                        } elseif($record->type == 'Telecommunication') {
                            return $record->flat->getAttribute('etisalat/du_number');
                        } elseif($record->type == 'lpg') {
                            return $record->flat->lpg_number;
                        } else {
                            return '--';
                        }
                    }),

                TextInput::make('amount')
                    ->required()
                    ->placeholder('Enter the total bill amount')
                    ->numeric(),
                DatePicker::make('month')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('m-Y')
                    ->helperText('Enter the month for which this bill is generated'),
                DatePicker::make('due_date')
                    ->required(),
                // DatePicker::make('uploaded_on')
                //     ->default(now())
                //     ->required(),
                Select::make('status')
                    ->helperText('Select the current status of the bill')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid'    => 'Paid',
                        'Overdue' => 'Overdue',
                    ])
                    ->live()
                    ->required(),
                Select::make('uploaded_by')
                    ->relationship('uploadedBy', 'first_name')
                    ->disabled()
                    ->preload()
                    ->visibleOn('edit')
                    ->searchable(),
                Select::make('status_updated_by')
                    ->relationship('statusUpdatedBy', 'first_name')
                    ->disabled()
                    ->live()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Bills')
            ->modifyQueryUsing(function ($query) {
                $buildingIds = DB::table('building_owner_association')
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->where('active', true)
                    ->pluck('building_id');

                $pmFlats = DB::table('property_manager_flats')
                    ->where('owner_association_id', auth()->user()?->owner_association_id)
                    ->where('active', true)
                    ->pluck('flat_id')
                    ->toArray();

                $flatIds = Flat::whereIn('building_id', $buildingIds)->pluck('id');

                if (auth()->user()->role->name == 'Property Manager'
                || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                ->pluck('role')[0] == 'Property Manager') {
                    return $query->whereIn('flat_id', $pmFlats)->orderBy('created_at', 'desc');
                } elseif (auth()->user()->role->name == 'OA') {
                    return $query->whereIn('flat_id', $flatIds)->orderBy('created_at', 'desc');
                }

                return $query->whereIn('flat_id', $flatIds)->orderBy('created_at', 'desc');
            })
            ->columns([
                TextColumn::make('flat.building.name')
                    ->label('Building'),
                TextColumn::make('flat.property_number')
                    ->label('Flat number'),
                TextColumn::make('month')
                    ->date()
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('M-Y');
                    }),
                TextColumn::make('amount')
                    ->numeric()
                    ->default('--'),
                TextColumn::make('due_date')
                    ->date(),
                TextColumn::make('status')
                    ->default('--')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending'                         => 'primary',
                        'Paid'                            => 'success',
                        'Overdue'                         => 'danger',
                        '--'                              => 'muted',
                    }),
            ])
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid'    => 'Paid',
                        'Overdue' => 'Overdue',
                    ]),
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1  => 'January',
                        2  => 'February',
                        3  => 'March',
                        4  => 'April',
                        5  => 'May',
                        6  => 'June',
                        7  => 'July',
                        8  => 'August',
                        9  => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $month): Builder => $query->whereMonth('due_date', $month)
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
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
            'index'  => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit'   => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
