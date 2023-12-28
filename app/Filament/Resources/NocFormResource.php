<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NocFormResource\Pages;
use App\Models\Forms\SaleNOC;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NocFormResource extends Resource
{
    protected static ?string $model = SaleNOC::class;
    protected static ?string $modelLabel = 'Sale NOC';
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
                    TextInput::make('unit_occupied_by')->disabled(),
                    TextInput::make('applicant')->disabled(),
                    TextInput::make('unit_area')->disabled(),
                    TextInput::make('sale_price')->disabled()->prefix('AED'),
                    TextInput::make('signing_authority_email')->disabled(),
                    TextInput::make('signing_authority_phone')->disabled(),
                    TextInput::make('signing_authority_name')->disabled(),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('User'),
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
                    DatePicker::make('service_charge_paid_till')
                        ->disabled()
                        ->date(),
                    Repeater::make('contacts')
                        ->disabled()
                        ->relationship()
                        ->schema([
                            TextInput::make('type'),
                            TextInput::make('first_name'),
                            TextInput::make('last_name')
                                ->visible(function (callable $get) {
                                    if ($get('last_name') != null) {
                                        return true;
                                    }
                                    return false;
                                }),
                            TextInput::make('email'),
                            TextInput::make('mobile'),
                            TextInput::make('emirates_id')
                                ->visible(function (callable $get) {
                                    if ($get('emirates_id') != null) {
                                        return true;
                                    }
                                    return false;
                                }),
                            TextInput::make('passport_number')
                                ->visible(function (callable $get) {
                                    if ($get('passport_number') != null) {
                                        return true;
                                    }
                                    return false;
                                }),
                            TextInput::make('visa_number')
                                ->visible(function (callable $get) {
                                    if ($get('visa_number') != null) {
                                        return true;
                                    }
                                    return false;
                                }),
                            FileUpload::make('emirates_document_url')
                                ->visible(function (callable $get) {
                                    if ($get('emirates_document_url') != null) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Emirates Document File')
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('visa_document_url')
                                ->visible(function (callable $get) {
                                    if ($get('visa_document_url') != null) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Visa Document File')
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('title_deed')
                                ->visible(function (callable $get) {
                                    if ($get('title_deed') != null) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Title deed')
                                ->downloadable(true)
                                ->openable(true),
                            FileUpload::make('passport_document_url')
                                ->visible(function (callable $get) {
                                    if ($get('passport_document_url') != null) {
                                        return true;
                                    }
                                    return false;
                                })
                                ->disk('s3')
                                ->directory('dev')
                                ->label('Passport Document File')
                                ->downloadable(true)
                                ->openable(true),
                        ])
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    FileUpload::make('cooling_receipt')
                        ->visible(function (callable $get) {
                            if ($get('cooling_receipt') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->disabled()
                        ->directory('dev')
                        ->label('Cooling Receipt')
                        ->downloadable(true)
                        ->openable(true)
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    FileUpload::make('cooling_soa')
                        ->visible(function (callable $get) {
                            if ($get('cooling_soa') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->disabled()
                        ->directory('dev')
                        ->label('Cooling Soa')
                        ->downloadable(true)
                        ->openable(true)
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    FileUpload::make('cooling_clearance')
                        ->visible(function (callable $get) {
                            if ($get('cooling_clearance') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->disabled()
                        ->directory('dev')
                        ->label('Cooling Clearance')
                        ->downloadable(true)
                        ->openable(true)
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    FileUpload::make('payment_receipt')
                        ->visible(function (callable $get) {
                            if ($get('payment_receipt') != null) {
                                return true;
                            }
                            return false;
                        })
                        ->disk('s3')
                        ->disabled()
                        ->directory('dev')
                        ->label('Payment Receipt')
                        ->downloadable(true)
                        ->openable(true)
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    Toggle::make('cooling_bill_paid')
                        ->disabled()
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    Toggle::make('service_charge_paid')
                        ->disabled()
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    Toggle::make('noc_fee_paid')
                        ->disabled()
                        ->columnSpan([
                            'sm' => '1',
                            'md' => '1',
                            'lg' => '2',
                        ]),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(function (SaleNOC $record) {
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
                        ->disabled(function (SaleNOC $record) {
                            return $record->status != null;
                        })
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->label('Unit Number')
                    ->default('NA'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('flat_id')
                    ->relationship('flat', 'property_number')
                    ->searchable()
                    ->preload()
                    ->label('Unit Number'),
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
            'index' => Pages\ListNocForms::route('/'),
            // 'view' => Pages\ViewNocForm::route('/{record}'),
            'edit' => Pages\EditNocForm::route('/{record}/edit'),
        ];
    }
}
