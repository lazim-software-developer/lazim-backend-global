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
        $expectedHeadings = ['unit_number', 'property_type', 'mollak_property_id', 'suit_area', 'actual_area', 'balcony_area',
                'applicable_area', 'parking_count', 'makhani_number', 'dewa_number', 'etisalat/du_number','btu/ac_number'];

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
            $exists         = Flat::where(['property_number' => $row['unit_number'], 'owner_association_id' => $this->oaId, 'building_id' => $this->buildingId])->exists();

            $requiredFields = $row['property_number'] != null && in_array($row['property_type'], ['Shop', 'Office', 'Unit'])
                                && is_numeric($row['parking_count']) ? true : false;

            if (!$requiredFields && $exists) {
                $notImported[] = $row['property_number'];
            } else {
                Flat::create([
                    'owner_association_id' => $this->oaId,
                    'building_id'          => $this->buildingId,
                    'property_number'      => $row['unit_number'],
                    'property_type'        => $row['property_type'],
                    'mollak_property_id'   => $row['mollak_property_id'],
                    'suit_area'            => $row['suit_area'],
                    'actual_area'          => $row['actual_area'],
                    'balcony_area'         => $row['balcony_area'],
                    'applicable_area'      => $row['applicable_area'],
                    'parking_count'        => $row['parking_count'],
                    'makhani_number'       => $row['makhani_number'],
                    'dewa_number'          => $row['dewa_number'],
                    'etisalat/du_number'   => $row['etisalat/du_number'],
                    'btu/ac_number'        => $row['btu/ac_number'],
                ]);
            }
        }
        if (!empty($notImported)) {
            Notification::make()
                ->title("Buildings imported successfully.")
                ->body('Not imported Buildings' . implode(', ', $notImported))
                ->success()
                ->send();

            return 'success';
        } else {
            Notification::make()
                ->title("Buildings imported successfully.")
                ->success()
                ->send();

            return 'success';
        }

    }
}
