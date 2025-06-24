<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessCardFormsDocumentResource\Pages;
use App\Models\Building\Building;
use App\Models\Forms\AccessCard;
use App\Models\Master\Role;
use App\Models\Order;
use App\Models\OwnerAssociation;
use DB;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AccessCardFormsDocumentResource extends Resource
{
    protected static ?string $model = AccessCard::class;

    protected static ?string $modelLabel      = 'Access card';
    protected static ?string $navigationGroup = 'Forms Document';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
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
                        ->label('Card type'),
                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->placeholder('Email'),
                    TextInput::make('mobile')
                        ->label('Mobile number')
                        ->disabled()
                        ->placeholder('Mobile Number'),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Building name'),
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
                    TextInput::make('reason')
                        ->label('Reason')
                        ->disabled()
                        ->readOnly(),
                    Textarea::make('parking_details')
                        ->visible(function (callable $get) {
                            if ($get('parking_details') != "Invalid parking details format") {
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
                        ->label('Vehicle registration'),
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
                            'requested' => 'Requested',
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        // ->disabled(function (AccessCard $record) {
                        //     return $record->status != null;
                        // })
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (callable $set, callable $get, ?string $state, ?Model $record) {
                            if ($state === 'approved') {
                                // Check if there's an existing order with payment status
                                $order = null;
                                if ($record) {
                                    $order = Order::where(['orderable_id' => $record->id, 'orderable_type' => AccessCard::class])->first();
                                }
                                
                                if ($order && $order->payment_status) {
                                    if($order->payment_status == 'NA'){
                                        $set('payment_status', 'Payment Initiate');
                                    }else{
                                        $set('payment_status', $order->payment_status);
                                    }
                                } else {
                                    $set('payment_status', 'Payment Initiate');
                                }
                            } else {
                                // Reset payment status when not approved
                                $set('payment_status', 'NA');
                            }
                        }),
                    TextInput::make('payment_amount')
                        ->label('Payment amount')
                        ->numeric()
                        ->required()
                        ->readOnly(function (callable $get) {
                            $order = Order::where(['orderable_id' => $get('id'), 'orderable_type' => AccessCard::class])->latest()->first();
                            return $order && ($order->payment_status === 'Payment Initiate' || $order->payment_status === 'Payment Under Process' || $order->payment_status === 'Payment Failed' || $order->payment_status === 'Payment Success');
                        })
                        ->formatStateUsing(function (?Model $record) {
                            return Order::where(['orderable_id' => $record->id, 'orderable_type' => AccessCard::class])->latest()->value('amount');
                        })
                        ->rules('numeric', 'The payment amount must be a number.')
                        ->visible(function (callable $get) {
                            return $get('status') === 'approved';
                        })
                        ->reactive(),
                    // Payment Status field with complete logic
                    Select::make('payment_status')
                        ->label('Payment status')
                        ->required()
                        ->options(function (callable $get) {
                            $orderpayment_status = Order::where(['orderable_id' => $get('id'), 'orderable_type' => AccessCard::class])->latest()->value('payment_status');
                            if ($orderpayment_status == 'NA') {
                                return [
                                    'Payment Initiate' => 'Payment Initiate',
                                ];
                            } elseif ($orderpayment_status == 'Payment Initiate') {
                                return [
                                    'Payment Initiate' => 'Payment Initiate',
                                    'Payment Success' => 'Payment Success',
                                    'Payment Failed' => 'Payment Failed',
                                    'Payment Under Process' => 'Payment Under Process',
                                ];
                            }
                            elseif ($orderpayment_status == 'Payment Under Process') {
                                return [
                                    'Payment Under Process' => 'Payment Under Process',
                                    'Payment Success' => 'Payment Success',
                                    'Payment Failed' => 'Payment Failed',
                                ];
                            }
                            elseif ($orderpayment_status == 'Payment Success') {
                                return [
                                    'Payment Success' => 'Payment Success',
                                ];
                            }
                            elseif ($orderpayment_status == 'Payment Failed') {
                                return [
                                    'Payment Failed' => 'Payment Failed',
                                ];
                            } else {
                                return [
                                    'NA' => 'NA',
                                    'Payment Initiate' => 'Payment Initiate',
                                    'Payment Success' => 'Payment Success',
                                    'Payment Failed' => 'Payment Failed',
                                    'Payment Under Process' => 'Payment Under Process',
                                ];
                            }
                        })
                        ->formatStateUsing(function (?Model $record) {
                            return Order::where(['orderable_id' => $record->id, 'orderable_type' => AccessCard::class])->latest()->value('payment_status');
                        })
                        ->visible(function (callable $get) {
                            return $get('status') === 'approved';
                        })
                        ->live(),
                    // TextInput::make('reason')
                    //     ->formatStateUsing(function (?Model $record, callable $get) {
                    //         $orderpayment_status = Order::where(['orderable_id' => $record->id, 'orderable_type' => AccessCard::class])->first()?->payment_status;
                    //         if ($orderpayment_status) {
                    //             return $orderpayment_status == 'requires_payment_method' ? 'Payment Failed' : $orderpayment_status;
                    //         }
                    //         return 'NA';
                    //     })
                    //     ->label('Payment status')
                    //     ->readOnly(),
                    Textarea::make('remarks')
                        ->rules(['max:250'])
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
                            'card_type'                   => 'Card type',
                            'email'                       => 'Email',
                            'mobile'                      => 'Mobile number',
                            'make_model'                  => 'Make and model',
                            'vehicle_color'               => 'Vehicle color',
                            'emirates_of_registration'    => 'Emirates of registration',
                            'parking_bay_number'          => 'Parking bay number',
                            'vehicle_registration_number' => 'Vehicle registration number',
                            'tenancy'                     => 'Tenancy / Ejari',
                            'vehicle_registration'        => 'Vehicle registration / Mulkiya',
                            'passport'                    => 'Passport / EID',
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
                TextColumn::make('ticket_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Ticket number'),
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
                    ->label('Unit number')
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
                    TextColumn::make('latestOrder')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return 'NA';
                        }
                        
                        $order = Order::where('orderable_id', $record->id)
                            ->where('orderable_type', AccessCard::class)
                            ->latest()
                            ->first();
                            
                        return $order ? $order->payment_status : 'NA';
                    })
                    ->label('Payment status')
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
                // ->relationship('building', 'name', function (Builder $query) {
                //     if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                //         $query->where('owner_association_id', auth()->user()?->owner_association_id);
                //     }

                // })
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (auth()->user()->role->name == 'Property Manager'
                        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                            ->pluck('role')[0] == 'Property Manager') {
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

            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ])])
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
            'edit'  => Pages\EditAccessCardFormsDocument::route('/{record}/edit'),
        ];
    }
}
