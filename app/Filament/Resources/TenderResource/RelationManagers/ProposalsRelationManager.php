<?php

namespace App\Filament\Resources\TenderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BuildingVendor;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Vendor\Contract;
use Filament\Tables\Actions\Action;
use App\Models\Vendor\ServiceVendor;
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
                TextColumn::make('tender.created_by')->searchable()->label('Tender Created By'),
                TextColumn::make('amount')->label('Amount'),
                TextColumn::make('submittedBy.name')->searchable()->label('Vendor Name'),
                TextColumn::make('submitted_on')->label('Submitted On'),
                TextColumn::make('status')->default('NA')->label('Status'),
            ])
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
                        $budgetId = Tender::where('id', $tenderId)->first()->budget_id;
                        $serviceId = Tender::find($tenderId)->service_id;
                        $buildingId = DB::table('budgets')->where('id', $budgetId)->pluck('building_id');
                        $budget_from = DB::table('budgets')->where('id', $budgetId)->pluck('budget_from')[0];
                        $budget_to = DB::table('budgets')->where('id', $budgetId)->pluck('budget_to')[0];


                        $contract = Contract::create([
                            'start_date' => $budget_from,
                            'end_date' => $budget_to,
                            'contract_type' => 'onetime',
                            'service_id' => $serviceId,
                            'vendor_id' => $record->vendor_id,
                            'building_id' => $buildingId[0],
                        ]);

                        $servicefind = ServiceVendor::all()->where('service_id',$serviceId)->where('vendor_id',$record->vendor_id)->first();
                        if($servicefind)
                        {
                            $servicefind->contract_id = $contract->id;
                            $servicefind->building_id = $buildingId[0];
                            $servicefind->save();
                        }
                        else{
                            $servicevendor = ServiceVendor::create([
                                'service_id' => $serviceId,
                                'vendor_id' => $record->vendor_id,
                                'active' => true,
                                'contract_id' => $contract->id,
                                'building_id' => $buildingId[0],
                            ]);
                            $servicevendor->contract_id = $contract->id;
                            $servicevendor->save();
                        }

                        BuildingVendor::create([
                            'vendor_id' => $record->vendor_id,
                            'active' => true,
                            'building_id' => $buildingId[0],
                            'contract_id' => $contract->id,
                            'start_date' => $budget_from,
                            'end_date' => $budget_to,
                        ]);
                        $record->status = 'approved';
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->status_updated_on = now();
                        $record->save();

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
