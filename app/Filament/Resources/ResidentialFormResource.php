<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentialFormResource\Pages;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\ResidentialForm;
use DB;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ResidentialFormResource extends Resource
{
    protected static ?string $model = ResidentialForm::class;

    protected static ?string $title          = 'Residential';
    protected static ?string $modelLabel     = 'Residential';
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
                                ->label('Unit number'),
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
                                ->label('Emirates ID')
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
                    ->columns(2)
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
                                'passport_url'        => 'Passport / EID',
                                'emirates_url'        => 'Email',
                                'title_deed_url'      => 'Mobile number',
                                'emirates_expires_on' => 'Emirates Expires Date',
                                'passport_expires_on' => 'Passport Expires Date',
                                'title_deed_number'   => 'Title Deed Number',
                                'emirates_id'         => 'Emirates Id',
                                'trn_number'          => 'TRN Number',
                                'office_number'       => 'Office Number',
                                'tenancy_contract'    => 'Tenancy / Ejari',
                                'number_of_adults'    => 'Number of adults',
                                'number_of_children'  => 'Number Of Children',
                                'unit_occupied_by'    => 'Unit Occupied By',
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
                    ->default('--')
                    ->label('Ticket number'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->label('Building')
                    ->default('--'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->label('Resident name')
                    ->default('--'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->label('Unit number')
                    ->default('--'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
            ])
            ->filters([
                // SelectFilter::make('user_id')
                //     ->relationship('user', 'first_name')
                //     ->searchable()
                //     ->preload()
                //     ->label('User'),
                SelectFilter::make('building_id')
                // ->relationship('building', 'name', function (Builder $query) {
                //     if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                //         $query->where('owner_association_id', Filament::getTenant()?->id);
                //     }

                // })
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (auth()->user()->role->name == 'Property Manager') {
                            $buildingIds = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');

                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');

                        }
                        $oaId = auth()->user()?->owner_association_id;
                        return Building::where('owner_association_id', $oaId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                // SelectFilter::make('flat_id')
                //     ->relationship('flat', 'property_number')
                //     ->searchable()
                //     ->preload()
                //     ->label('Unit Number'),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                ExportBulkAction::make(),
            ])
            ->actions([

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
            'index' => Pages\ListResidentialForms::route('/'),
            // 'view' => Pages\ViewResidentialForm::route('/{record}'),
            'edit'  => Pages\EditResidentialForm::route('/{record}/edit'),
        ];
    }
}
