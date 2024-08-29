<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Asset;
use App\Models\BuildingVendor;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    protected function afterCreate(): void
    {
        // dd($this->data,$this->record->id);
        $servicefind = ServiceVendor::all()->where('service_id', $this->record->service_id)->where('vendor_id', $this->record->vendor_id)->first();
            if ($servicefind && $servicefind->building_id == null) {
                $servicefind->contract_id = $this->record->id;
                $servicefind->building_id = $this->record->building_id;
                $servicefind->save();
            } else {
                $servicevendor = ServiceVendor::create([
                    'service_id' => $this->record->service_id,
                    'vendor_id' => $this->record->vendor_id,
                    'active' => true,
                    'contract_id' => $this->record->id,
                    'building_id' => $this->record->building_id,
                ]);
                // $servicevendor->contract_id = $this->record->id;
                // $servicevendor->save();
            }

            BuildingVendor::create([
                'vendor_id' => $this->record->vendor_id,
                'active' => true,
                'building_id' => $this->record->building_id,
                'contract_id' => $this->record->id,
                'start_date' => $this->record->start_date,
                'end_date' => $this->record->end_date,
            ]);
            // $record->status_updated_by = auth()->user()->id;
            // $record->status_updated_on = now();
            // $record->save();

            $technicianVendorIds = DB::table('service_technician_vendor')
                ->where('service_id', $this->record->service_id)
                ->pluck('technician_vendor_id');

            $assets = Asset::where('building_id', $this->record->building_id)->where('service_id', $this->record->service_id)->get();

            foreach ($assets as $asset) {
                $asset->vendors()->syncWithoutDetaching([$this->record->vendor_id]);
                $technicianIds = TechnicianVendor::whereIn('id', $technicianVendorIds)->where('vendor_id', $this->record->vendor_id)->where('active', true)->pluck('technician_id');
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
                            'vendor_id' => $this->record->vendor_id,
                            'building_id' => $asset->building_id,
                            'active' => 1,
                        ]);
                    } else {
                        Log::info("No technicians to add", []);
                    }
                }
            }
    }
}
