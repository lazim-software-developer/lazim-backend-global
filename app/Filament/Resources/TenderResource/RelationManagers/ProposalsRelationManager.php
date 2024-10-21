<?php

namespace App\Filament\Resources\TenderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\BuildingVendor;
use App\Models\Vendor\Contract;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Tender;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Proposal;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor\ServiceVendor;
use App\Models\Accounting\Budgetitem;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'proposals';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])->schema([
                TextInput::make('amount')
                    ->label('Amount')
                    ->prefix('AED')
                    ->disabled(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->label('Vendor Name')
                    ->disabled(),
                TextInput::make('submitted_on')
                    ->disabled()
                    ->default(now()),
                ViewField::make('Budget amount')
                    ->view('forms.components.budgetamount'),
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (Proposal $record) {
                        return $record->status != null;
                    })
                    ->required()
                    ->searchable()
                    ->live(),
                TextInput::make('remarks')
                    ->rules(['max:55'])
                    ->visible(function (callable $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    })
                    ->disabled(function (Proposal $record) {
                        return $record->status != null;
                    })
                    ->required(),
                FileUpload::make('document')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->label('Document'),

            ])
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('tender.created_by')->searchable()->label('Tender Created By'),
                TextColumn::make('amount')->label('Amount'),
                ViewColumn::make('Budget amount')->view('tables.columns.budgetamount')->alignCenter(),
                TextColumn::make('submittedBy.name')->searchable()->label('Vendor Name'),
                TextColumn::make('submitted_on')->label('Submitted On'),
                TextColumn::make('status')->default('NA')->label('Status'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Model $record) {
                        if ($record->status == 'approved') {
                            $tenderId = $record->tender_id;
                            $tenderAmount = $record->amount;
                            $tender = Tender::find($tenderId);
                            $oa_id = DB::table('building_owner_association')->where('building_id', $tender->building_id)->where('active', true)->first()?->owner_association_id;
                            $budget = Budget::find($tender->budget_id);
                            // dd($budget->budget_from);
                            $budgetamount = Budgetitem::where(['budget_id' => $tender->budget_id, 'service_id' => $tender->service_id])->first();

                            $contract = Contract::create([
                                'start_date' => $budget->budget_from,
                                'amount' => $tenderAmount,
                                'end_date' => $budget->budget_to,
                                'contract_type' => $tender->tender_type,
                                'service_id' => $tender->service_id,
                                'vendor_id' => $record->vendor_id,
                                'building_id' => $tender->building_id,
                                'budget_amount' => $budgetamount ? $budgetamount->total : 0,
                                'owner_association_id' => $oa_id
                            ]);

                            $servicefind = ServiceVendor::all()->where('service_id', $tender->service_id)->where('vendor_id', $record->vendor_id)->first();
                            if ($servicefind->building_id == null) {
                                $servicefind->contract_id = $contract->id;
                                $servicefind->building_id = $tender->building_id;
                                $servicefind->save();
                            } else {
                                $servicevendor = ServiceVendor::create([
                                    'service_id' => $tender->service_id,
                                    'vendor_id' => $record->vendor_id,
                                    'active' => true,
                                    'contract_id' => $contract->id,
                                    'building_id' => $tender->building_id,
                                ]);
                                $servicevendor->contract_id = $contract->id;
                                $servicevendor->save();
                            }

                            BuildingVendor::create([
                                'vendor_id' => $record->vendor_id,
                                'active' => true,
                                'building_id' => $tender->building_id,
                                'contract_id' => $contract->id,
                                'start_date' => $budget->budget_from,
                                'end_date' => $budget->budget_to,
                                'owner_association_id' => $oa_id
                            ]);
                            $record->status_updated_by = auth()->user()->id;
                            $record->status_updated_on = now();
                            $record->save();

                            //Inserting vendor record into lazim-accounts database
                            $connection = DB::connection('lazim_accounts');
                            $vendor     = Vendor::find($record->vendor_id);
                            $user       = User::find($vendor->owner_id);
                            $creator    = $connection->table('users')->where(['type' => 'building', 'building_id' => $tender->building_id])->first();
                            $exists = $connection->table('venders')->where(['lazim_vendor_id' => $vendor->id,
                                                                            'building_id'   => $tender->building_id]
                                                                        )->count();

                            if (isset($contract, $vendor, $creator) && $exists==0) {
                                $connection->table('venders')->insert([
                                    'vender_id'       => $connection->table('venders')->where('created_by', $creator->id)->orderByDesc('vender_id')->first()?->vender_id + 1,
                                    'name'            => $vendor->name,
                                    'email'           => substr($creator->name, 0, 2) . $user->email,
                                    'password'        => '',
                                    'contact'         => $user->phone,
                                    'created_by'      => $creator->id,
                                    'is_enable_login' => 0,
                                    'created_at'      => now(),
                                    'updated_at'      => now(),
                                    'billing_name'    => $tender->building->name,
                                    'billing_country' => 'UAE',
                                    'billing_city'    => 'Dubai',
                                    'billing_address' => $vendor->address_line_1,
                                    'shipping_name'    => $tender->building->name,
                                    'shipping_country' => 'UAE',
                                    'shipping_city'    => 'Dubai',
                                    'shipping_address' => $vendor->address_line_1,
                                    'lazim_vendor_id' => $vendor->id,
                                    'building_id'     => $tender->building_id,
                                ]);
                                $connection->table('oa_vendor')->insert([
                                    'lazim_owner_association_id' => $vendor->owner_association_id,
                                    'vendor_id'                  => $connection->table('venders')->where('lazim_vendor_id', $vendor->id)->first()?->id,
                                ]);
                            }

                            $technicianVendorIds = DB::table('service_technician_vendor')
                                ->where('service_id', $contract->service_id)
                                ->pluck('technician_vendor_id');

                            $assets = Asset::where('building_id', $contract->building_id)->where('service_id', $contract->service_id)->get();

                            foreach ($assets as $asset) {
                                $asset->vendors()->syncWithoutDetaching([$contract->vendor_id]);
                                $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)->where('vendor_id', $contract->vendor_id)->where('active', true)->pluck('technician_id');
                                if ($technicianIds) {
                                    $assignees = User::whereIn('id', $technicianIds)
                                        ->withCount(['assets' => function ($query) {
                                            $query->where('active', true);
                                        }])
                                        ->orderBy('assets_count', 'asc')
                                        ->get();
                                    $selectedTechnician = $assignees->first();

                                    if ($selectedTechnician) {
                                        $assigned = TechnicianAssets::create([
                                            'asset_id' => $asset->id,
                                            'technician_id' => $selectedTechnician->id,
                                            'vendor_id' => $contract->vendor_id,
                                            'building_id' => $asset->building_id,
                                            'active' => 1,
                                            'owner_association_id' => $oa_id
                                        ]);
                                    } else {
                                        Log::info("No technicians to add", []);
                                    }
                                }
                            }
                        }
                        if ($record->status == 'rejected') {
                            $record->status_updated_by = auth()->user()->id;
                            $record->status_updated_on = now();
                            $record->save();
                        }
                    }),
            ]);
    }
}
