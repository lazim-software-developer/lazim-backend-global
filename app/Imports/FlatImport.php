<?php

namespace App\Imports;

use App\Models\Building\Flat;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        // Define the expected headings
        $expectedHeadings = ['unit_number',
                'property_type',
                // 'mollak_property_id',
                'suit_area',
                'actual_area',
                'balcony_area',
                // 'applicable_area',
                'plot_number',
                'parking_count',
                'makhani_number',
                'dewa_number',
                'btuetisalat_number',
                'btuac_number'];

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
        $extractedHeadings = array_keys($rows->first()->toArray());
        // dd($extractedHeadings);

        // Check if all expected headings are present in the extracted headings
        $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);

        if (!empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("Missing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }

        $notImported = [];
        foreach ($rows as $row) {
            // Normalize property type case
            if (!empty($row['property_type'])) {
                $row['property_type'] = ucfirst(strtolower($row['property_type']));
            }

            $exists = Flat::where([
                'property_number' => $row['unit_number'],
                'owner_association_id' => $this->oaId,
                'building_id' => $this->buildingId
            ])->exists();

            $errors = [];

            // Required field validations
            if (empty($row['unit_number'])) {
                $errors[] = 'Unit number is required';
            }
            if (empty($row['property_type'])) {
                $errors[] = 'Property type is required';
            }

            // Property type validation
            if (!empty($row['property_type']) && !in_array($row['property_type'], ['Shop', 'Office', 'Unit'])) {
                $errors[] = 'Property type must be Shop, Office, or Unit';
            }

            // Numeric field validations
            $numericFields = [
                'suit_area' => 'Suit area',
                'actual_area' => 'Actual area',
                'balcony_area' => 'Balcony area',
                'parking_count' => 'Parking count',
                'plot_number' => 'Plot number',
                'makhani_number' => 'Makhani number',
                'dewa_number' => 'Dewa number',
                'btuetisalat_number' => 'BTU/Etisalat number',
                'btuac_number' => 'BTU/AC number'
            ];

            foreach ($numericFields as $field => $label) {
                if (!empty($row[$field]) && !is_numeric($row[$field])) {
                    $errors[] = "$label must be numeric";
                }
            }

            if (!empty($errors)) {
                $notImported[] = $row['unit_number'] . ' (' . implode(', ', $errors) . ')';
                continue;
            }

            if ($exists) {
                $notImported[] = $row['unit_number'] . ' (already exists)';
            } else {
                Flat::create([
                    'owner_association_id' => $this->oaId,
                    'building_id' => $this->buildingId,
                    'property_number' => $row['unit_number'],
                    'property_type' => $row['property_type'],
                    'suit_area' => $row['suit_area'] ?: null,
                    'actual_area' => $row['actual_area'] ?: null,
                    'balcony_area' => $row['balcony_area'] ?: null,
                    'plot_number' => $row['plot_number'] ?: null,
                    'parking_count' => $row['parking_count'] ?: null,
                    'makhani_number' => $row['makhani_number'] ?: null,
                    'dewa_number' => $row['dewa_number'] ?: null,
                    'etisalat/du_number' => $row['btuetisalat_number'] ?: null,
                    'btu/ac_number' => $row['btuac_number'] ?: null,
                ]);
            }
        }

        if (!empty($notImported)) {
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
