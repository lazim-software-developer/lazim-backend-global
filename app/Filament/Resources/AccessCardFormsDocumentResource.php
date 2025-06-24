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
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Order;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
                        ->label('Flat'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->disabled()
                        ->preload()
                        ->searchable()
                        ->label('User'),
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
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        ->disabled(function (AccessCard $record) {
                            return $record->status != null;
                        })
                        ->required()
                        ->searchable()
                        ->live(),
                    TextInput::make('reason')
                        ->formatStateUsing(function (?Model $record) {
                            $orderpayment_status = Order::where(['orderable_id' => $record->id, 'orderable_type' => AccessCard::class])->first()?->payment_status;
                            if ($orderpayment_status) {
                                return $orderpayment_status == 'requires_payment_method' ? 'Payment Failed' : $orderpayment_status;
                            }
                            return 'NA';
                        })
                        ->label('Payment status')
                        ->readOnly(),
                    TextInput::make('remarks')
                        ->rules(['max:150'])
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
                TextColumn::make('ticket_number')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->label('Ticket number'),
                TextColumn::make('card_type')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Flat')
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
                    ->default('Pending')
                    ->limit(50),
                TextColumn::make('orders')
                    ->formatStateUsing(fn($state) => json_decode($state) ? (json_decode($state)->payment_status == 'requires_payment_method' ? 'Payment Failed' : json_decode($state)->payment_status) : 'NA')
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
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListAccessCardFormsDocuments::route('/'),
            // 'view' => Pages\ViewAccessCardFormsDocument::route('/{record}'),
            'edit' => Pages\EditAccessCardFormsDocument::route('/{record}/edit'),
        ];
    }
}
