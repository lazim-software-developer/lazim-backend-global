<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Forms\AccessCard;
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
use App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

class AccessCardFormsDocumentResource extends Resource
{
    protected static ?string $model = AccessCard::class;

    protected static ?string $modelLabel = 'Access card';
    protected static ?string $navigationGroup = 'Forms Document';
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
                    TextInput::make('card_type')
                        ->disabled()
                        ->label('Card Type'),
                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->placeholder('Email'),
                    TextInput::make('mobile')
                        ->label('Mobile Number')
                        ->disabled()
                        ->placeholder('Mobile Number'),
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
                    Textarea::make('parking_details')
                        ->visible(function (callable $get) {
                            if ($get('parking_details') != "Invalid parking details format" ) {
                                return true;
                            }
                            return false;
                        })
                        ->disabled()
                        ->rows(10)
                        ->placeholder('No Parking Details'),
                    FileUpload::make('tenancy')
                        ->visible(function (callable $get) {
                            if ($get('tenancy') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Tenancy / Ejari'),
                    FileUpload::make('vehicle_registration')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->visible(function (callable $get) {
                            if ($get('vehicle_registration') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->label('Vehicle Registration'),
                    FileUpload::make('title_deed')
                        ->visible(function (callable $get) {
                            if ($get('title_deed') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Title Deed'),
                    FileUpload::make('passport')
                        ->visible(function (callable $get) {
                            if ($get('passport') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->downloadable(true)
                        ->openable(true)
                        ->label('Passport / EID'),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        ->disabled(function (AccessCard $record) {
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
                        ->disabled(function (AccessCard $record) {
                            return $record->status != null;
                        })
                        ->required(),
                    // If the form is rejected, we need to capture which fields are rejected
                    CheckboxList::make('rejected_fields')
                        ->label('Please select rejected fields')
                        ->options([
                            'card_type' => 'Card type',
                            'email' => 'Email',
                            'mobile' => 'Mobile number',
                            'make_model' => 'Make and model',
                            'vehicle_color' => 'Vehicle color',
                            'emirates_of_registration' => 'Emirates of registration',
                            'parking_bay_number' => 'Parking bay number',
                            'vehicle_registration_number' => 'Vehicle registration number',
                            'tenancy' => 'Tenancy / Ejari',
                            'vehicle_registration' => 'Vehicle registration / Mulkiya',
                            'passport' => 'Passport / EID',
                        ])->columns(4)
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
                TextColumn::make('card_type')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Unit Number')
                    ->limit(50),
                // ImageColumn::make('tenancy')
                //     ->label('Tenancy')
                //     ->square()
                //     ->alignCenter()
                //     ->disk('s3'),
                // ImageColumn::make('vehicle_registration')
                //     ->label('Vehicle Registration')
                //     ->square()
                //     ->alignCenter()
                //     ->disk('s3'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }

                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
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
            'index' => Pages\ListAccessCardFormsDocuments::route('/'),
            // 'view' => Pages\ViewAccessCardFormsDocument::route('/{record}'),
            'edit' => Pages\EditAccessCardFormsDocument::route('/{record}/edit'),
        ];
    }
}
