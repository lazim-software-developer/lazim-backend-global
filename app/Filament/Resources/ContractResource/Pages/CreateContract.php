<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Asset;
use App\Models\BuildingVendor;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\ServiceVendor;
use App\Models\Vendor\Vendor;
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

        //Inserting vendor record into lazim-accounts database
        $connection = DB::connection('lazim_accounts');
        $vendor     = Vendor::find($this->record->vendor_id);
        $user       = User::find($vendor->owner_id);
        $creator    = $connection->table('users')->where(['type' => 'building', 'building_id' => $this->record->building_id])->first();
        $exists     = $connection->table('venders')->where(
            [
                'lazim_vendor_id' => $vendor->id,
                'building_id' => $this->record->building_id
            ]
        )->count();
        if (isset($vendor, $creator) && $exists == 0) {
            $connection->table('venders')->insert([
                'vender_id'        => $connection->table('venders')->where('created_by', $creator->id)->orderByDesc('vender_id')->first()?->vender_id + 1,
                'name'             => $vendor->name,
                'email'            => substr($creator->name, 0, 2) . $user->email,
                'password'         => '',
                'contact'          => $user->phone,
                'created_by'       => $creator->id,
                'is_enable_login'  => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
                'billing_name'     => $this->record->building->name,
                'billing_country'  => 'UAE',
                'billing_city'     => 'Dubai',
                'billing_address'  => $vendor->address_line_1,
                'shipping_name'    => $this->record->building->name,
                'shipping_country' => 'UAE',
                'shipping_city'    => 'Dubai',
                'shipping_address' => $vendor->address_line_1,
                'lazim_vendor_id'  => $vendor->id,
                'building_id'      => $this->record->building_id,
            ]);
            $connection->table('oa_vendor')->insert([
                'lazim_owner_association_id' => $vendor->owner_association_id,
                'vendor_id'                  => $connection->table('venders')->where('lazim_vendor_id', $vendor->id)->first()?->id,
            ]);
        }
    }
}
