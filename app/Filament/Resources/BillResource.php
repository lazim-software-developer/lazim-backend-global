<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Carbon\Carbon;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                        'lpg'               => 'lpg',
                    ])
                    ->live()
                    ->reactive()
                    ->required(),

                TextInput::make('dewa_number')
                    ->label('DEWA Number')
                    ->visible(fn(Get $get) => $get('type') == 'DEWA')
                    ->numeric()
                    ->rules([
                        'min_digits:10',
                        'max_digits:10',
                    ])
                    ->unique('bills', 'dewa_number', ignoreRecord: true)
                    ->validationMessages([
                        'min_digits' => 'The DEWA number must be 10 characters long.',
                        'max_digits' => 'The DEWA number must be 10 characters long.',
                        'unique'     => 'The DEWA number has already been taken.',
                    ])
                    ->placeholder('Enter the DEWA number')
                    ->required(),

                Select::make('building_id')
                    ->label('Building')
                    ->afterStateUpdated(fn(Set $set) => $set('flat_id', null))
                    ->options(function () {
                        if (auth()->user()->role->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (auth()->user()->role->name == 'Property Manager') {
                            $buildingIds = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');

                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');
                        } else {
                            $oaId = auth()->user()?->owner_association_id;
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
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
                TextInput::make('amount')
                    ->required()
                    ->visible(fn(Get $get) => $get('type') !== 'DEWA')
                    ->placeholder('Enter the total bill amount')
                    ->numeric(),
                DatePicker::make('month')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('m-Y')
                    ->helperText('Enter the month for which this bill is generated'),
                DatePicker::make('due_date')
                    ->visible(fn(Get $get) => $get('type') !== 'DEWA')
                    ->required(),
                // DatePicker::make('uploaded_on')
                //     ->default(now())
                //     ->required(),
                Select::make('status')
                    ->visible(fn(Get $get) => $get('type') !== 'DEWA')
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
                    ->visible(fn(Get $get) => $get('type') !== 'DEWA')
                    ->disabled()
                    ->live()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $buildingIds = DB::table('building_owner_association')
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->where('active', true)
                    ->pluck('building_id');

                $flatIds = Flat::whereIn('building_id', $buildingIds)->pluck('id');

                return $query->whereIn('flat_id', $flatIds);
            })
            ->columns([
                TextColumn::make('flat.property_number')
                    ->label('Flat number'),
                TextColumn::make('month')
                    ->date()
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('m-Y');
                    }),
                TextColumn::make('amount')
                    ->numeric()
                    ->default('--')
                    ->visible(fn() => request()->query('activeTab') !== 'DEWA'),
                TextColumn::make('due_date')
                    ->date()
                    ->visible(fn() => request()->query('activeTab') !== 'DEWA'),
                TextColumn::make('status')
                    ->default('--')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending'                         => 'primary',
                        'Paid'                            => 'success',
                        'Overdue'                         => 'danger',
                        '--'                              => 'muted',
                    })
                    ->visible(fn() => request()->query('activeTab') !== 'DEWA'),
                TextColumn::make('uploadedBy.first_name'),
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
            ])
            ->modifyQueryUsing(function ($query) {
                $query->orderBy('created_at', 'desc');
            });
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
