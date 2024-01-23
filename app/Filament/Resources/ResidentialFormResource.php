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
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('unit_occupied_by')
                        ->disabled()
                        ->label('Unit Occupied By'),
                    TextInput::make('passport_number')
                        ->label('Passport Number')
                        ->disabled()
                        ->placeholder('Passport Number'),
                    TextInput::make('name')
                        ->label('Name of Resident')
                        ->disabled()
                        ->placeholder('Name of Resident'),
                    TextInput::make('number_of_children')
                        ->label('Number Of Children')
                        ->disabled()
                        ->placeholder('Number Of Children'),
                    TextInput::make('number_of_adults')
                        ->label('Number Of Adults')->disabled(),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Building Name'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Unit Number'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->disabled()
                        ->preload()
                        ->searchable()
                        ->label('User'),
                    TextInput::make('office_number')
                        ->label('Office Number')
                        ->visible(function (callable $get) {
                            if ($get('office_number') != null) {
                                return true;
                            }
                            return false;
                        })->disabled(),
                    TextInput::make('trn_number')
                        ->label('TRN Number')
                        ->visible(function (callable $get) {
                            if ($get('trn_number') != null) {
                                return true;
                            }
                            return false;
                        })->disabled(),
                    TextInput::make('emirates_id')
                        ->label('Emirates Id')->disabled(),
                    TextInput::make('title_deed_number')
                        ->label('Title Deed Number')->disabled(),
                    Textarea::make('emergency_contact')
                        ->label('Emergency Contact')
                        ->disabled()
                        ->rows(10)
                        ->placeholder('No Parking Details'),
                    TextInput::make('passport_expires_on')
                        ->label('Passport Expires On')->disabled(),
                    TextInput::make('emirates_expires_on')
                        ->label('Emirates Expires On')->disabled(),
                    FileUpload::make('title_deed_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Title Deed File')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('title_deed_url') != null) {
                                return true;
                            }
                            return false;
                        }),
                    FileUpload::make('emirates_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->label('Emirates File')
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('emirates_url') != null) {
                                return true;
                            }
                            return false;
                        }),
                    FileUpload::make('passport_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->label('Passport File')
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('passport_url') != null) {
                                return true;
                            }
                            return false;
                        }),
                    FileUpload::make('tenancy_contract')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->label('Tenancy / Ejari File')
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('tenancy_contract') != null) {
                                return true;
                            }
                            return false;
                        }),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(function (ResidentialForm $record) {
                            return $record->status != null;
                        })
                        ->required()
                        ->searchable()
                        ->live(),
                    TextInput::make('remarks')
                        ->rules(['max:255'])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                        ->disabled(function (ResidentialForm $record) {
                            return $record->status != null;
                        })
                        ->required(),
                    // If the form is rejected, we need to capture which fields are rejected
                    CheckboxList::make('rejected_fields')
                        ->label('Please select rejected fields')
                        ->options([
                            'passport_url' => 'Passport / EID',
                            'emirates_url' => 'Email',
                            'title_deed_url' => 'Mobile number',
                            'emirates_expires_on' => 'Emirates Expires Date',
                            'passport_expires_on' => 'Passport Expires Date',
                            'title_deed_number' => 'Title Deed Number',
                            'emirates_id' => 'Emirates Id',
                            'trn_number' => 'TRN Number',
                            'office_number' => 'Office Number',
                            'tenancy_contract' => 'Tenancy / Ejari',
                            'number_of_adults' => 'Number of adults',
                            'number_of_children' => 'Number Of Children',
                            'unit_occupied_by' => 'Unit Occupied By',
                        ])->columns(4)
                        ->columnSpan([
                            'sl' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        }),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->searchable()
                    ->label('Building')
                    ->default('NA'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->label('Resident Name')
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->label('Unit Number')
                    ->default('NA'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->filters([
                // SelectFilter::make('user_id')
                //     ->relationship('user', 'first_name')
                //     ->searchable()
                //     ->preload()
                //     ->label('User'),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }

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
            'edit' => Pages\EditResidentialForm::route('/{record}/edit'),
        ];
    }
}
