<?php

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Models\Asset;
use App\Models\User\User;
use App\Models\BuildingVendor;
use App\Models\Vendor\Contract;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Tender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor\ServiceVendor;
use App\Models\Accounting\Budgetitem;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProposalResource;

class EditProposal extends EditRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $record = $this->record;
        if ($record->status == 'approved') {
            $tenderId = $record->tender_id;
            $tenderAmount = $record->amount;
            $tender = Tender::find($tenderId);
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
            ]);
            $record->status_updated_by = auth()->user()->id;
            $record->status_updated_on = now();
            $record->save();

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
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
