<?php

namespace App\Imports;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\MollakTenant;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class MyClientImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $iterate = count($collection);
        $int = 1;
        while ($iterate > 1) {
            $createbuild = Building::firstOrCreate(
                [
                    'name' => $collection[$int][1], 
                    'property_group_id' => $collection[$int][0],
                    'owner_association_id' => auth()->user()->owner_association_id,
                ],
                [
                    'address_line1' => 'Define',
                ]
            );

            $createflat = Flat::firstOrCreate(
                [
                    'property_number' => $collection[$int][3],
                    'mollak_property_id' => $collection[$int][2],
                    'building_id' => $createbuild->id,
                ],
                [
                    'property_type' => 'owner',
                    'owner_association_id' => auth()->user()->owner_association_id,
                ]
            );

            MollakTenant::create([
                'building_id' => $createbuild->id,
                'flat_id' => $createflat->id,
                'contract_number' => $collection[$int][4],
                'name' => $collection[$int][5],
                'emirates_id' => $collection[$int][6],
                'license_number' => $collection[$int][7],
                'mobile' => preg_replace('/0/','+971', $collection[$int][8],1),
                'email' => $collection[$int][9],
                'start_date' => $collection[$int][10],
                'end_date' => $collection[$int][11],
                'contract_status' => $collection[$int][12],
            ]);
            $int = $int + 1;
            $iterate = $iterate - 1;
        }
    }
}
