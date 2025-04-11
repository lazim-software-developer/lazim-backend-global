<?php
namespace App\Imports;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FlatImport implements ToCollection, WithHeadingRow
{

    public function __construct(protected $oaId, protected $buildingId)
    {
        //
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // Define the expected headings (without asterisks)
        $expectedHeadings = [
            'unit_number',
            'property_type',
            'suit_area',
            'actual_area',
            'balcony_area',
            'plot_number',
            'parking_count',
            'makani_number',
            'dewa_number',
            'duetisalat_number',
            'btuac_number',
            'lpg_number',
        ];

        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Extract the headings from the first row
        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Clean the extracted headings (remove asterisks)
        $extractedHeadings = array_map(function ($heading) {
            return trim(str_replace('*', '', $heading));
        }, array_keys($rows->first()->toArray()));

        // Clean the expected headings
        $expectedHeadings = array_map(function ($heading) {
            return trim(str_replace('*', '', $heading));
        }, $expectedHeadings);

        // Check if all expected headings are present in the extracted headings
        $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);

        if (! empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("Missing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }

        $totalParkingCount = 0;
        $notImported = [];
        foreach ($rows as $row) {
            // Handle fields with or without asterisk in column names
            $unitNumber   = $row['unit_number*'] ?? $row['unit_number'] ?? null;
            $propertyType = $row['property_type*'] ?? $row['property_type'] ?? null;

            // Normalize property type case
            if (! empty($propertyType)) {
                $propertyType = ucfirst(strtolower($propertyType));
            }

            $exists = Flat::where([
                'property_number'      => $unitNumber,
                'building_id'          => $this->buildingId,
            ])->exists();

            $errors = [];

            // Required field validations
            if (empty($unitNumber)) {
                $errors[] = 'Unit number is required';
            }
            if (empty($propertyType)) {
                $errors[] = 'Property type is required';
            }

            // Property type validation
            if (! empty($propertyType) && ! in_array($propertyType, ['Shop', 'Office', 'Unit'])) {
                $errors[] = 'Property type must be Shop, Office, or Unit';
            }

            // Updated numeric field validations
            $numericFields = [
                'suit_area'         => 'Suit area',
                'actual_area'       => 'Actual area',
                'balcony_area'      => 'Balcony area',
                'parking_count'     => 'Parking count',
                'plot_number'       => 'Plot number',
                'makani_number'     => 'Makani number',
                'dewa_number'       => 'Dewa number',
                'duetisalat_number' => 'BTU/Etisalat number',
                'btuac_number'      => 'BTU/AC number',
                'lpg_number'        => 'LPG number',
            ];

            foreach ($numericFields as $field => $label) {
                if (! empty($row[$field])) {
                    // Allow numbers and special characters but no alphabets
                    if (preg_match('/[a-zA-Z]/', $row[$field])) {
                        $errors[] = "$label cannot contain alphabetic characters";
                    }
                }
            }

            // Add parking count validation
            $parkingCount = $row['parking_count'] ?: null;
            if ($parkingCount !== null) {
                // Add new flat's parking count
                $totalParkingCount = $totalParkingCount + (int) $parkingCount;
            }

            if (! empty($errors)) {
                $notImported[] = $unitNumber . ' (' . implode(', ', $errors) . ')';
                continue;
            }

            if ($exists) {
                // Now use the flat's ID when creating the property_manager_flats record
                $flat = Flat::where([
                    'property_number'      => $unitNumber,
                    'building_id'          => $this->buildingId,
                ])->first();
                DB::table('property_manager_flats')->insert([
                    'owner_association_id' => $this->oaId,
                    'flat_id'              => $flat->id,
                    'active'               => true,
                ]);
            } else {
                // Store the newly created flat in a variable
                $flat = Flat::create([
                    'owner_association_id' => $this->oaId,
                    'building_id'          => $this->buildingId,
                    'property_number'      => $unitNumber,
                    'property_type'        => $propertyType,
                    'suit_area'            => $row['suit_area'] ?: null,
                    'actual_area'          => $row['actual_area'] ?: null,
                    'balcony_area'         => $row['balcony_area'] ?: null,
                    'plot_number'          => $row['plot_number'] ?: null,
                    'parking_count'        => $parkingCount,
                    'makhani_number'       => $row['makani_number'] ?: null,
                    'dewa_number'          => $row['dewa_number'] ?: null,
                    'etisalat/du_number'   => $row['duetisalat_number'] ?: null,
                    'btu/ac_number'        => $row['btuac_number'] ?: null,
                    'lpg_number'           => $row['lpg_number'] ?: null,
                ]);

                // Now use the flat's ID when creating the property_manager_flats record
                DB::table('property_manager_flats')->insert([
                    'owner_association_id' => $this->oaId,
                    'flat_id'              => $flat->id,
                    'active'               => true,
                ]);
            }
        }
        $building = Building::where('building_id',$this->buildingId);

        $buildingCount = $building->parking_count;
        $building->update(['parking_count' => ($buildingCount + $totalParkingCount)]);

        if (! empty($notImported)) {
            Notification::make()
                ->title("Couldn't upload Flats.")
                ->body('Not imported Flats: ' . implode(', ', $notImported))
                ->danger()
                ->send();

            return 'failure';
        } else {
            Notification::make()
                ->title("Flats imported successfully.")
                ->success()
                ->send();

            return 'success';
        }

    }
}
