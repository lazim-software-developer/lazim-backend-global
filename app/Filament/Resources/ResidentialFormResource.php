<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\ResidentialForm;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\ResidentialFormResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ResidentialFormResource extends Resource
{
    protected static ?string $model = ResidentialForm::class;

    protected static ?string $title = 'Residential';
    protected static ?string $modelLabel = 'Residential';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Resident Details Section
                Section::make('Resident Details')
                    ->schema([
                        Grid::make([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextInput::make('unit_occupied_by')
                                ->disabled()
                                ->label('Unit occupied by'),
                            TextInput::make('passport_number')
                                ->label('Passport number')
                                ->disabled()
                                ->placeholder('Passport Number'),
                            TextInput::make('name')
                                ->label('Name of resident')
                                ->disabled()
                                ->placeholder('Name of Resident'),
                            TextInput::make('number_of_children')
                                ->label('Number of children')
                                ->disabled()
                                ->placeholder('Number Of Children'),
                            TextInput::make('number_of_adults')
                                ->label('Number of adults')
                                ->disabled(),
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('Building'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('Flat'),
                            Select::make('user_id')
                                ->rules(['exists:users,id'])
                                ->relationship('user', 'first_name')
                                ->disabled()
                                ->preload()
                                ->searchable()
                                ->label('User'),
                            TextInput::make('office_number')
                                ->label('Office number')
                                ->visible(function (callable $get) {
                                    return $get('office_number') != null;
                                })->disabled(),
                            TextInput::make('trn_number')
                                ->label('TRN number')
                                ->visible(function (callable $get) {
                                    return $get('trn_number') != null;
                                })->disabled(),
                            TextInput::make('emirates_id')
                                ->label('National ID')
                                ->disabled(),
                            TextInput::make('title_deed_number')
                                ->label('Title Deed number')
                                ->disabled(),
                            TextInput::make('passport_expires_on')
                                ->label('Passport expires on')
                                ->disabled(),
                            TextInput::make('emirates_expires_on')
                                ->label('Emirates expires on')
                                ->disabled(),
                            Textarea::make('emergency_contact')
                                ->label('Emergency contact')
                                ->disabled()
                                ->rows(10)
                                ->placeholder('No Parking Details'),
                        ]),
                    ]),

                // Document Uploads Section
                Section::make('Documents')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('title_deed_url')
                            ->disk('s3')
                            ->directory('dev')
                            ->label('Title Deed File')
                            ->disabled()
                            ->downloadable(true)
                            ->openable(true)
                            ->visible(function (callable $get) {
                                return $get('title_deed_url') != null;
                            }),
                        FileUpload::make('emirates_url')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->label('Emirates File')
                            ->downloadable(true)
                            ->openable(true)
                            ->visible(function (callable $get) {
                                return $get('emirates_url') != null;
                            }),
                        FileUpload::make('passport_url')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->label('Passport File')
                            ->downloadable(true)
                            ->openable(true)
                            ->visible(function (callable $get) {
                                return $get('passport_url') != null;
                            }),
                        FileUpload::make('tenancy_contract')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->label('Tenancy / Ejari File')
                            ->downloadable(true)
                            ->openable(true)
                            ->visible(function (callable $get) {
                                return $get('tenancy_contract') != null;
                            }),
                    ]),

                // Status and Remarks Section
                Section::make('Approval Details')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->disabled(function (ResidentialForm $record) {
                                return $record->status != null;
                            })
                            ->required()
                            ->searchable()
                            ->live(),

                        TextInput::make('remarks')
                            ->rules(['max:150'])
                            ->visible(function (callable $get) {
                                return $get('status') == 'rejected';
                            })
                            ->disabled(function (ResidentialForm $record) {
                                return $record->status != null;
                            })
                            ->required(),

                        // Rejected Fields Section
                        CheckboxList::make('rejected_fields')
                            ->label('Please select rejected fields')
                            ->options([
                                'passport_url' => 'Passport / EID',
                                'emirates_url' => 'Email',
                                'title_deed_url' => 'Mobile number',
                                'emirates_expires_on' => 'Emirates Expires Date',
                                'passport_expires_on' => 'Passport Expires Date',
                                'title_deed_number' => 'Title Deed Number',
                                'emirates_id' => 'National ID',
                                'trn_number' => 'TRN Number',
                                'office_number' => 'Office Number',
                                'tenancy_contract' => 'Tenancy / Ejari',
                                'number_of_adults' => 'Number of adults',
                                'number_of_children' => 'Number Of Children',
                                'unit_occupied_by' => 'Unit Occupied By',
                            ])->columns(4)
                            ->visible(function (callable $get) {
                                return $get('status') == 'rejected';
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->label('Ticket number'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->sortable()
                    ->label('Building')
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->sortable()
                    ->label('Flat')
                    ->default('NA'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->sortable()
                    ->label('Resident name')
                    ->default('NA'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('Pending')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->filters([
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->searchable()
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    $buildingId = DB::table('building_owner_association')->where('owner_association_id', auth()->user()?->owner_association_id)->where('active', true)->pluck('building_id');
                                    return Building::whereIn('id', $buildingId)->pluck('name', 'id');
                                }
                            })
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('flat', null);
                            }),

                        Select::make('flat')
                            ->searchable()
                            ->options(function (callable $get) {
                                $buildingId = $get('Building'); // Get selected building ID
                                if (empty($buildingId)) {
                                    return [];
                                }

                                return Flat::where('building_id', $buildingId)->pluck('property_number', 'id');
                            }),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['Building'])) {
                            $flatIds = Flat::where('building_id', $data['Building'])->pluck('id');
                            $query->whereIn('flat_id', $flatIds);
                        }
                        if (!empty($data['flat'])) {
                            $query->where('flat_id', $data['flat']);
                        }

                        return $query;
                    }),

                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'NA' => 'Pending'
                            ])
                            ->label('Status')
                            ->placeholder('Select Status')
                            ->required(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedStatus = $data['status'] ?? null;

                        if ($selectedStatus === 'NA') {
                            $query->whereNull('status')
                                ->orWhereNotIn('status', ['approved', 'rejected']);
                        } elseif ($selectedStatus !== null) {
                            $query->where('status', $selectedStatus);
                        }

                        return $query;
                    })


            ])
            ->filtersFormColumns(3)
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                ExportBulkAction::make(),
            ])
            ->actions([]);
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
            'index' => Pages\ListResidentialForms::route('/'),
            // 'view' => Pages\ViewResidentialForm::route('/{record}'),
            'edit' => Pages\EditResidentialForm::route('/{record}/edit'),
        ];
    }
}
