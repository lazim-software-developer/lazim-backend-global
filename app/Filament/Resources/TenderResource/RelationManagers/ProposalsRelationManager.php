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
use App\Models\Accounting\Tender;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Proposal;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor\ServiceVendor;
use App\Models\Accounting\Budgetitem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'proposals';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label('Amount')
                    ->prefix('AED')
                    ->disabled(),
                TextInput::make('submitted_by')
                    ->label('Vendor Name')
                    ->disabled()
                    ->default(1),
                TextInput::make('submitted_on')
                    ->disabled()
                    ->default(now()),
                FileUpload::make('document')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->label('Document'),
                Select::make('status')
                ->options([
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->searchable()
                ->required()
                ->placeholder('Status'),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('tender.created_by')->searchable()->label('Tender Created By'),
                TextColumn::make('amount')->label('Amount'),
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
                // Tables\Actions\EditAction::make(),
                Action::make('Approve')
                    ->visible(fn($record) => $record->status == null)
                    ->button()
                    ->form([
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->required(),
                    ])
                    ->fillForm(fn(Proposal $record): array => [
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Proposal $record, array $data): void {

                        $tenderId = Proposal::where('vendor_id', $record->vendor_id)->where('status', null)->first()->tender_id;
                        $tenderAmount = Proposal::where('vendor_id', $record->vendor_id)->where('status', null)->first()->amount;
                        $budgetId = Tender::where('id', $tenderId)->first()->budget_id;
                        $serviceId = Tender::find($tenderId)->service_id;
                        $contractType = Tender::find($tenderId)->tender_type;
                        $buildingId = Tender::find($tenderId)->building_id;
                        $budget_from = DB::table('budgets')->where('id', $budgetId)->pluck('budget_from')[0];
                        $budget_to = DB::table('budgets')->where('id', $budgetId)->pluck('budget_to')[0];
                        $budget_amount = Budgetitem::where('budget_id',$budgetId)->where('service_id',$serviceId)->first()->total;

                        $contract = Contract::create([
                            'start_date' => $budget_from,
                            'amount'=>$tenderAmount,
                            'end_date' => $budget_to,
                            'contract_type' => $contractType,
                            'service_id' => $serviceId,
                            'vendor_id' => $record->vendor_id,
                            'building_id' => $buildingId,
                            'budget_amount' => $budget_amount,
                        ]);

                        $servicefind = ServiceVendor::all()->where('service_id',$serviceId)->where('vendor_id',$record->vendor_id)->first();
                        if($servicefind->building_id == null)
                        {
                            $servicefind->contract_id = $contract->id;
                            $servicefind->building_id = $buildingId;
                            $servicefind->save();
                        }
                        else{
                            $servicevendor = ServiceVendor::create([
                                'service_id' => $serviceId,
                                'vendor_id' => $record->vendor_id,
                                'active' => true,
                                'contract_id' => $contract->id,
                                'building_id' => $buildingId,
                            ]);
                            $servicevendor->contract_id = $contract->id;
                            $servicevendor->save();
                        }

                        BuildingVendor::create([
                            'vendor_id' => $record->vendor_id,
                            'active' => true,
                            'building_id' => $buildingId,
                            'contract_id' => $contract->id,
                            'start_date' => $budget_from,
                            'end_date' => $budget_to,
                        ]);
                        $record->status = 'approved';
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->status_updated_on = now();
                        $record->save();

                        $technicianVendorIds = DB::table('service_technician_vendor')
                                 ->where('service_id',$contract->service_id)
                                 ->pluck('technician_vendor_id');

                        $assets = Asset::where('building_id',$contract->building_id)->where('service_id',$contract->service_id)->get();
                        
                        foreach($assets as $asset){
                            $asset->vendors()->syncWithoutDetaching([$contract->vendor_id]);
                            $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)->where('vendor_id',$contract->vendor_id)->where('active', true)->pluck('technician_id');
                            if ($technicianIds){
                                $assignees = User::whereIn('id',$technicianIds)
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
                                    ]);
                                } else {
                                    Log::info("No technicians to add", []);
                                }
                            }
                        }

                    })
                    ->slideOver(),
                Action::make('Reject')
                    ->visible(fn($record) => $record->status == null)
                    ->button()
                    ->form([
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->required(),
                    ])
                    ->fillForm(fn(Proposal $record): array => [
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Proposal $record, array $data): void {
                        $record->status = 'rejected';
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->status_updated_on = now();
                        $record->save();
                    })
                    ->slideOver()
            ]);
    }
}
