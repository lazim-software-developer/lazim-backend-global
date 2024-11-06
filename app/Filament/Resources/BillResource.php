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
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                        'Telecommunication' => 'Telecommunication',
                        'lpg'               => 'lpg',
                    ])
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
                    ->visibleOn('edit')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flat.property_number')
                    ->label('Flat number'),
                TextColumn::make('month')
                    ->date()
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('m-Y');
                    }),
                TextColumn::make('amount')
                    ->numeric(),
                TextColumn::make('due_date')
                    ->date(),
                // TextColumn::make('uploaded_on')
                //     ->date(),
                // TextColumn::make('statusUpdatedBy.first_name'),
                TextColumn::make('uploadedBy.first_name'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending'                         => 'primary',
                        'Paid'                            => 'success',
                        'Overdue'                         => 'danger'
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid'    => 'Paid',
                        'Overdue' => 'Overdue',
                    ]),
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
