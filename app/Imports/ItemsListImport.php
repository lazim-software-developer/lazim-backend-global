<?php

namespace App\Imports;

use App\Models\Item;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsListImport implements ToCollection, WithHeadingRow
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
        $expectedHeadings = [
            'item_name',
            'quantity',
            'description',
        ];

        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Extract the headings from the first row
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
        } else {

            $missingFieldsRows = [];

            foreach ($rows as $index => $row) {
                // Check if any of the required fields are null
                if (empty($row['item_name']) || empty($row['quantity']) || empty($row['description'])) {
                    $missingFieldsRows[] = $index + 1; // Add the row number to the array
                }
            }

            if (!empty($missingFieldsRows)) {
                // If there are rows with missing fields, show an error message with the row numbers
                Notification::make()
                    ->title("Upload valid excel file.")
                    ->danger()
                    ->body("Required fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                    ->send();
                return 'failure';
            }
            foreach ($rows as $row) {
                $buildingId = $this->buildingId;

                Item::Create([
                    'name'        => $row['item_name'],
                    'quantity'    => $row['quantity'],
                    'description' => $row['description'],
                    'building_id' => $buildingId,
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
