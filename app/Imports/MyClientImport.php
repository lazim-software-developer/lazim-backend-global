<?php

namespace App\Imports;

use App\Models\MollakTenant;
use App\Models\Building\Flat;
use App\Models\Building\Building;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MyClientImport implements ToCollection, WithHeadingRow
{
    protected $buildingId;
    public function __construct($buildingId)
    {
        $this->buildingId = $buildingId;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // Define the expected headings
        $expectedHeadings = [
            'property_group', 'building', 'mollak_id', 'unit_number',
            'contract_number', 'tenant_name', 'emirates_id', 'license_number',
            'mobile', 'email', 'start_date', 'end_date', 'contract_status'
        ];

        // Extract the headings from the first row
        $extractedHeadings = array_keys($rows->first()->toArray());

        // Check if all expected headings are present in the extracted headings
        $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);

        if (!empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file. Missing headings: " . implode(', ', $missingHeadings))
                ->danger()
                ->send();
            return 'failure';
        } else {
            foreach ($rows as $row) {
                $building = Building::find($this->buildingId)->first();
                $createflat = Flat::firstOrCreate(
                    [
                        'property_number' => $row['unit_number'],
                        'mollak_property_id' => $row['mollak_id'],
                        'building_id' => $this->buildingId,
                    ],
                    [
                        'property_type' => 'owner',
                        'owner_association_id' => $building->owner_association_id,
                    ]
                );
                MollakTenant::firstOrCreate([
                    'building_id' => $this->buildingId,
                    'flat_id' => $createflat->id,
                    'contract_number' => $row['contract_number'],
                    'name' => $row['tenant_name'],
                    'emirates_id' => $row['emirates_id'],
                    'license_number' => $row['license_number'],
                    'mobile' => preg_replace('/0/', '971', $row['mobile'], 1),
                    'email' => $row['email'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'contract_status' => $row['contract_status'],
                ]);
            }
            Notification::make()
                ->title("Details uploaded successfully")
                ->success()
                ->send();
            return 'success';
        }
    }
}
