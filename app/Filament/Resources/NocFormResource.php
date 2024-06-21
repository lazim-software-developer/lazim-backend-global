<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Forms\SaleNOC;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NocFormResource\Pages;
use Closure;

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
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        ->disabled(function (SaleNOC $record) {
                            return $record->status != null;
                        })
                                ->visible(function(SaleNOC $record){
                                    return $record->submit_status == 'buyer_uploaded';
                                })
                        ->required()
                        ->searchable()
                        ->live(),
                    TextInput::make('remarks')
                        ->rules(['max:150'])
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
                    FileUpload::make('admin_document')
                        ->disk('s3')
                        ->directory('dev')
                        ->rules(['file','mimes:pdf',function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if($value->getSize()/ 1024 > 2048){
                                    $fail('The document must not be greater than 2MB.');
                                }
                            };
                        },])
                        ->openable(true)->required()
                        ->downloadable(true)
                        ->disabled(function($record){
                            return $record->admin_document  ;
                        })->helperText('Once a document is uploaded, it cannot be modified.')
                        ->visible(function (callable $get) {
                            if ($get('status') == 'approved') {
                                return true;
                            }
                            return false;
                        })
                        ->label('Document')
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
