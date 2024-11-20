<?php

namespace App\Filament\Resources\FlatTenantResource\RelationManagers;

use App\Models\Building\FlatTenant;
use App\Models\RentalCheque;
use App\Models\RentalDetail;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'rentalDetails';

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
                                Select::make('flat_id')
                                    ->disabled()
                                    ->relationship('flat', 'property_number')
                                    ->label('Flat number')
                                    ->default($this->ownerRecord->flat_id)
                                    ->placeholder('Select a flat number'),
                                Select::make('number_of_cheques')
                                    ->native(false)
                                    ->required()
                                    ->disabledOn('edit')
                                    ->placeholder('Select the number of cheques')
                                    ->options([
                                        '1' => '1',
                                        '2' => '2',
                                        '3' => '3',
                                        '4' => '4',
                                        '6' => '6',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($set, $state) {
                                        $set('cheques_count', $state);

                                        // Create an array of empty cheque entries
                                        $cheques = array_fill(0, (int)$state, [
                                            'cheque_number' => '',
                                            'amount' => '',
                                            'due_date' => '',
                                            'status' => 'Upcoming',
                                            'mode_payment' => 'Cheque'
                                        ]);

                                        $set('cheques', $cheques);
                                    }),
                                    // ->afterStateUpdated(fn($set, $state) => $set('cheques_count', $state)),
                                DatePicker::make('contract_start_date')
                                    ->rules(['date'])
                                    ->default(function () {
                                        $startDate = FlatTenant::where('id', $this->ownerRecord->id)
                                            ->first()?->start_date;

                                        return $startDate ? Carbon::parse($startDate) : null;
                                    })
                                    ->disabled()
                                    ->placeholder('Select contract start date'),
                                DatePicker::make('contract_end_date')
                                    ->rules(['date'])
                                    ->default(function () {
                                        $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                        return $endDate ? Carbon::parse($endDate) : null;
                                    })
                                    ->disabled(function () {
                                        $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                        return $endDate ? true : false;
                                    })
                                    ->disabledOn('edit')
                                    ->required(function () {
                                        $endDate = FlatTenant::where('id', $this->ownerRecord->id)->first()?->end_date;
                                        return $endDate ? false : true;
                                    })
                                    ->placeholder('Select contract end date'),
                                TextInput::make('admin_fee')
                                    ->nullable()
                                    ->disabledOn('edit')
                                    ->minValue(0)
                                    ->placeholder('Enter the Admin fee')
                                    ->numeric()
                                    ->suffix('AED')
                                    ->maxLength(10),
                                TextInput::make('other_charges')
                                    ->nullable()
                                    ->disabledOn('edit')
                                    ->suffix('AED')
                                    ->minValue(0)
                                    ->numeric()
                                    ->maxLength(10),
                                TextInput::make('advance_amount')
                                    ->required()
                                    ->maxLength(10)
                                    ->suffix('AED')
                                    ->numeric()
                                    ->placeholder('Enter advance amount'),
                                Select::make('advance_amount_payment_mode')
                                    ->native(false)
                                    ->required()
                                    ->disabledOn('edit')
                                    ->options([
                                        'Online' => 'Online',
                                        'Cheque' => 'Cheque',
                                        'Cash'   => 'Cash',
                                    ])
                                    ->placeholder('Select payment mode'),
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
                            ->addable(false) // Disable manual adding since we're auto-creating
                            ->deletable(false) // Disable deletion to maintain the required number
                            ->defaultItems(0)
                            // ->addable(fn($context) => $context !== 'edit')
                            // ->deletable(fn($context) => $context !== 'edit')
                            // ->minItems(fn(callable $get) => $get('cheques_count') ?? 0)
                            // ->maxItems(function (callable $get) {
                            //     $chequesCount = $get('cheques_count');
                            //     return $chequesCount !== null ? $chequesCount : PHP_INT_MAX;
                            // })
                            // ->validationMessages([
                            //     'minItems' => 'Please enter all the cheques details by clicking on \'Add to cheques\'',
                            // ])
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('cheque_number')
                                            ->numeric()
                                            ->minLength(0)
                                            ->disabledOn('edit')
                                            ->required()
                                            ->minLength(6)
                                            ->maxLength(12)
                                            ->placeholder('Enter cheque number'),
                                        TextInput::make('amount')
                                            ->maxLength(20)
                                            ->numeric()
                                            ->disabledOn('edit')
                                            ->minLength(0)
                                            ->required()
                                            ->placeholder('Enter amount'),
                                        DatePicker::make('due_date')
                                            ->rules(['date'])
                                            ->disabledOn('edit')
                                            ->required()
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
                                        // Repeater::make('comments')
                                        //     ->deletable(fn($context) => $context !== 'edit')
                                        //     ->simple(
                                        //         TextInput::make('comments')
                                        //             ->nullable(),
                                        //     ),
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
            ->columns([
                TextColumn::make('contract_start_date'),
                TextColumn::make('contract_end_date'),
                TextColumn::make('number_of_cheques'),
                TextColumn::make('advance_amount')
                ->default('NA'),
                TextColumn::make('admin_fee')
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
            // ->visible(function () {
            //     $rentalDetail = RentalDetail::where('flat_tenant_id', $this->ownerRecord->id)->first();

            //     if (!$rentalDetail) {
            //         return true;
            //     }

            //     $endDate = $rentalDetail->contract_end_date;
            //     return $endDate < Carbon::now()->format('Y-m-d');
            // })
            ->action(function (array $data) {
                // dd($data);
                $this->handleCustomActionSave($data);
            })
            ->form(function (Form $form) {
                return $this->form($form);
            });
    }

    private function handleCustomActionSave(array $data)
    {
        // dd($data);
        $startDate    = $this->oldFormState['mountedTableActionsData'][0]['contract_start_date'];
        $endDate      = $this->oldFormState['mountedTableActionsData'][0]['contract_end_date'];
        $rentalDetail = RentalDetail::create([
            'flat_id'                     => $data['flat_id'],
            'flat_tenant_id'              => $this->ownerRecord->id,
            'number_of_cheques'           => $data['number_of_cheques'],
            'admin_fee'                   => $data['admin_fee'] ?? null,
            'other_charges'               => $data['other_charges'] ?? null,
            'advance_amount'              => $data['advance_amount'],
            'advance_amount_payment_mode' => $data['advance_amount_payment_mode'],
            'status'                      => $data['status'],
            'contract_start_date'         => $startDate,
            'contract_end_date'           => $endDate,
            'created_by'                  => auth()->user()->id,
            'status_updated_by'           => auth()->user()->id,
            'property_manager_id'         => auth()->user()->owner_association_id,

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
    }
}
