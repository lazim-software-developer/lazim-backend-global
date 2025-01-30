<?php
namespace App\Filament\Resources\FlatTenantResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\Building\FlatTenant;
use App\Models\RentalCheque;
use App\Models\RentalDetail;
use App\Models\User\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RentalDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'rentalDetails';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->role === 'Tenant';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rental Details')
                    ->description('Enter the Rental Details.')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('building_id')
                                    ->disabled()
                                    ->default(fn() => $this->ownerRecord->building_id)
                                    ->label('Building')
                                    ->options(function ($record) {
                                        if ($record) {
                                            return [$record->flat->building_id => $record->flat->building->name];
                                        }
                                        return [$this->ownerRecord->building_id => $this->ownerRecord->building->name];
                                    })
                                    ->afterStateHydrated(function (Select $component, $record) {
                                        if ($record) {
                                            $component->state($record->flat->building_id);
                                        }
                                    })
                                    ->dehydrated(false)
                                    ->reactive(),
                                Select::make('flat_id')
                                    ->disabled()
                                    ->relationship('flat', 'property_number')
                                    ->label('Flat number')
                                    ->default($this->ownerRecord->flat_id),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('admin_fee')
                                    ->nullable()
                                    ->lazy()
                                    ->required()
                                    ->reactive() // Make this reactive to trigger updates
                                    ->disabledOn('edit')
                                    ->minValue(0)
                                    ->label('Contract amount')
                                    ->validationMessages([
                                        'required' => 'The Contract amount is required.',
                                    ])
                                    ->placeholder('Enter the Contract amount')
                                    ->numeric()
                                    ->suffix('AED')
                                    // ->maxLength(10)
                                    ->afterStateUpdated(function ($get, $set, $state) {
                                        // Get the number of cheques
                                        $numberOfCheques = $get('number_of_cheques');

                                        if ($numberOfCheques && $state) {
                                            // Calculate the default amount per cheque
                                            $defaultAmount = round($state / $numberOfCheques, 2);

                                            // Update cheque amounts
                                            $cheques = $get('cheques');
                                            if (is_array($cheques)) {
                                                foreach ($cheques as $index => $cheque) {
                                                    // Only update if the amount hasn't been manually modified
                                                    if (! isset($cheque['amount_manually_edited'])) {
                                                        $set("cheques.{$index}.amount", $defaultAmount);
                                                    }
                                                }
                                            }
                                        }
                                    }),

                                Select::make('number_of_cheques')
                                    ->native(false)
                                    ->required()
                                    ->disabledOn('edit')
                                    ->validationMessages([
                                        'required' => 'The number of cheques is required.',
                                    ])
                                    ->placeholder('Select the number of cheques')
                                    ->options([
                                        '1'  => '1',
                                        '2'  => '2',
                                        '3'  => '3',
                                        '4'  => '4',
                                        '6'  => '6',
                                        '12' => '12',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($get, $set, $state) {
                                        $set('cheques_count', $state);

                                        // Get the admin fee for even distribution
                                        $adminFee = $get('admin_fee');

                                        // Create an array of empty cheque entries
                                        $cheques = array_fill(0, (int) $state, [
                                            'cheque_number' => '',
                                            'amount'        => $adminFee ? round($adminFee / $state, 2) : '',
                                            'due_date'      => '',
                                            'status'        => 'Upcoming',
                                            'mode_payment'  => 'Cheque',
                                        ]);

                                        $set('cheques', $cheques);
                                    }),

                                DatePicker::make('contract_start_date')
                                    ->rules(['date'])
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'The Contract Start date is required.',
                                    ])
                                    ->label('Contract Start Date')
                                    ->default(function () {
                                        $startDate = FlatTenant::where('id', $this->ownerRecord->id)
                                            ->first()?->start_date;

                                        return $startDate ? Carbon::parse($startDate) : null;
                                    })
                                    ->disabledOn('edit')
                                    ->placeholder('Select contract start date'),

                                DatePicker::make('contract_end_date')
                                    ->rules(['date'])
                                    ->label('Contract End Date')
                                    ->after('contract_start_date')
                                    ->required()
                                    ->validationMessages([
                                        'after'    => 'The "Contract End" date must be after the "Contract Start" date.',
                                        'required' => 'The Contract End date is required.',
                                    ])
                                    ->default(function () {
                                        $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                        return $endDate ? Carbon::parse($endDate) : null;
                                    })
                                    ->disabled(function () {
                                        $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                        return $endDate ? true : false;
                                    })
                                    ->disabledOn('edit')
                                // ->required(function () {
                                //     $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                //     return $endDate ? false : true;
                                // })
                                    ->placeholder('Select contract end date'),

                                TextInput::make('advance_amount')
                                    ->required()
                                    ->maxLength(10)
                                    ->suffix('AED')
                                    ->disabledOn('edit')
                                    ->label('Security Deposit')
                                    ->numeric()
                                    ->validationMessages([
                                        'required' => 'The Security Deposit is required.',
                                    ])
                                    ->placeholder('Enter the Security Deposit'),

                                Select::make('advance_amount_payment_mode')
                                    ->native(false)
                                    ->required()
                                    ->label('Security Deposit Payment Mode')
                                    ->validationMessages([
                                        'required' => 'The Security Deposit Payment Mode is required.',
                                    ])
                                    ->disabledOn('edit')
                                    ->options([
                                        'Online' => 'Online',
                                        'Cheque' => 'Cheque',
                                        'Cash'   => 'Cash',
                                    ])
                                    ->placeholder('Select payment mode'),

                                TextInput::make('admin_charges')
                                    ->nullable()
                                    ->suffix('AED')
                                    ->minValue(0)
                                    ->numeric()
                                    ->maxLength(10)
                                    ->placeholder('Enter the Admin charges'),

                                TextInput::make('brokerage')
                                    ->nullable()
                                    ->suffix('AED')
                                    ->minValue(0)
                                    ->numeric()
                                    ->maxLength(10)
                                    ->placeholder('Enter the Brokerage amount'),

                                TextInput::make('other_charges')
                                    ->nullable()
                                    ->suffix('AED')
                                    ->minValue(0)
                                    ->numeric()
                                    ->maxLength(10),

                                Select::make('status')
                                    ->default('Active')
                                    ->required()
                                    ->label('Contract Status')
                                    ->native(false)
                                    ->options([
                                        'Active'            => 'Active',
                                        'Contract ended'    => 'Ended',
                                        'Contract extended' => 'Extended',
                                    ])
                                    ->placeholder('Select status'),
                            ]),
                    ]),
                Section::make('Cheque Details')
                    ->hiddenOn('edit')
                    ->description('Enter the Cheque Details.')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Repeater::make('cheques')
                            ->required()
                            ->addable(false)   // Disable manual adding since we're auto-creating
                            ->deletable(false) // Disable deletion to maintain the required number
                            ->defaultItems(0)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('cheque_number')
                                            ->numeric()
                                            ->disabledOn('edit')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'The Cheque number is required.',
                                            ])
                                            ->minLength(6)
                                            ->maxLength(12)
                                            ->placeholder('Enter cheque number'),

                                        TextInput::make('amount')
                                            ->numeric()
                                            ->rules(['numeric', 'regex:/^\d{1,17}(\.\d{1,2})?$/']) // Allows up to 17 digits before decimal and 2 after
                                            ->disabledOn('edit')
                                            ->minValue(0)
                                            ->required()
                                            ->placeholder('Enter amount')
                                            ->afterStateUpdated(function ($set) {
                                                // Mark that the amount has been manually edited
                                                $set('amount_manually_edited', true);
                                            }),

                                        DatePicker::make('due_date')
                                            ->rules(['date'])
                                            ->disabledOn('edit')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'The Due date is required.',
                                            ])
                                            ->placeholder('Select due date'),

                                        Select::make('status')
                                            ->default('Upcoming')
                                            ->required()
                                            ->visibleOn('edit')
                                            ->native(false)
                                            ->options([
                                                'Overdue'  => 'Overdue',
                                                'Paid'     => 'Paid',
                                                'Upcoming' => 'Upcoming',
                                            ])
                                            ->placeholder('Select cheque status'),

                                        Select::make('mode_payment')
                                            ->label('Payment Mode')
                                            ->default('Cheque')
                                            ->visibleOn('edit')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'Online' => 'Online',
                                                'Cheque' => 'Cheque',
                                                'Cash'   => 'Cash',
                                            ])
                                            ->placeholder('Select payment mode'),

                                        Select::make('cheque_status')
                                            ->visibleOn('edit')
                                            ->native(false)
                                            ->options([
                                                'Cancelled' => 'Cancelled',
                                                'Bounced'   => 'Bounced',
                                                'Paid'      => 'Paid',
                                            ])
                                            ->placeholder('Select cheque status'),

                                        TextInput::make('payment_link')
                                            ->url()
                                            ->visibleOn('edit')
                                            ->nullable()
                                            ->maxLength(200)
                                            ->placeholder('Enter payment link'),

                                        Textarea::make('comments')
                                            ->visibleOn('edit')
                                            ->nullable()
                                            ->maxLength(200)
                                            ->placeholder('Enter comments'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->latest();
            })
            ->columns([
                TextColumn::make('contract_start_date')
                    ->label('Contract Start Date'),
                TextColumn::make('contract_end_date')
                    ->label('Contract End Date'),
                TextColumn::make('number_of_cheques'),
                TextColumn::make('advance_amount')
                    ->label('Security Deposit')
                    ->default('NA'),
                TextColumn::make('admin_fee')
                    ->label('Contract amount')
                    ->default('NA'),
                TextColumn::make('other_charges')
                    ->default('NA'),
                TextColumn::make('status'),
            ])
            ->filters([])
            ->headerActions([
                $this->getCustomAction(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->form(fn(Form $form, $record) => $this->form($form)),
                Action::make('viewCheques')
                    ->label('View Cheques')
                    ->url('/app/rental-cheques')
                    ->icon('heroicon-o-eye'),
            ]);

    }

    private function getCustomAction(): Action
    {
        return Action::make('customCreate')
            ->label('Add Rental Details')
            ->action(function (array $data, Action $action) {
                // Validate cheque amounts sum matches admin fee
                if (isset($data['cheques']) && isset($data['admin_fee'])) {
                    $chequesSum = collect($data['cheques'])->sum('amount');
                    if ($chequesSum != $data['admin_fee']) {
                        Notification::make()
                            ->title('Incorrect Cheque Amounts')
                            ->body('The sum of cheque amounts (' . $chequesSum . ') must be equal to the contract amount (' . $data['admin_fee'] . ')')
                            ->danger()
                            ->send();

                        $action->halt(); // This prevents the modal from closing
                        return;
                    }
                }

                $this->handleCustomActionSave($data);
            })
            ->visible(function () {
                return false;
                // $latestRentalDetail = RentalDetail::where('flat_tenant_id', $this->ownerRecord->id)
                //     ->orderBy('contract_end_date', 'desc')
                //     ->first();

                // if (! $latestRentalDetail) {
                //     return true;
                // }

                // $endDate = $latestRentalDetail->contract_end_date;
                // return $endDate < Carbon::now()->format('Y-m-d');
            })
            ->form(function (Form $form) {
                return $this->form($form);
            });
    }

    private function handleCustomActionSave(array $data)
    {
        $startDate = $this->ownerRecord->start_date?->format('Y-m-d');
        $endDate   = $this->ownerRecord->end_date?->format('Y-m-d');

        $rentalDetail = RentalDetail::create([
            'flat_id'                     => $data['flat_id'],
            'flat_tenant_id'              => $this->ownerRecord->id,
            'number_of_cheques'           => $data['number_of_cheques'],
            'admin_fee'                   => $data['admin_fee'] ?? null,
            'other_charges'               => $data['other_charges'] ?? null,
            'advance_amount'              => $data['advance_amount'],
            'admin_charges'               => $data['admin_charges'] ?? null,
            'brokerage'                   => $data['brokerage'] ?? null,
            'advance_amount_payment_mode' => $data['advance_amount_payment_mode'],
            'status'                      => $data['status'],
            'contract_start_date'         => $data['contract_start_date'] ?? $startDate,
            'contract_end_date'           => $data['contract_end_date'] ?? $endDate,
            'created_by'                  => auth()->user()->id,
            'status_updated_by'           => auth()->user()->id,
            'property_manager_id'         => auth()->user()->id,

        ]);

        if (isset($data['cheques']) && is_array($data['cheques'])) {
            foreach ($data['cheques'] as $cheque) {
                RentalCheque::create([
                    'rental_detail_id'  => $rentalDetail->id,
                    'cheque_number'     => $cheque['cheque_number'],
                    'amount'            => $cheque['amount'],
                    'due_date'          => $cheque['due_date'],
                    'status'            => $cheque['status'] ?? 'Upcoming',
                    'status_updated_by' => auth()->user()->id,
                    'mode_payment'      => $cheque['mode_payment'] ?? 'null',
                    'cheque_status'     => $cheque['cheque_status'] ?? null,
                    'payment_link'      => $cheque['payment_link'] ?? null,
                    // 'comments'          => json_encode($cheque['comments']) ?? [],
                ]);
            }
        }

        Notification::make()
            ->title('Success')
            ->body('Rental details have been created successfully.')
            ->success()
            ->send();
    }
}
