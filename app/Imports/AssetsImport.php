<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetsImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'asset_name',
            'item_name',
            'warranties_count',
            'active_warranties_count',
            'jobs_count',
            'expenses',
        ];

        if($rows->first()== null){
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Check if the file is empty
        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Asset List and Expenses\nYou have uploaded an empty file")
                ->send();
            throw new Exception();
        }

        // Extract headings from the first row
        $extractedHeadings = array_keys($rows->first()->toArray());

        // Check for missing headings
        $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);
        if (!empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Asset List and Expenses\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }

        $filteredRows = $rows->filter(function ($row) {
            return !empty($row['asset_name']) ||
            !empty($row['item_name']) ||
            !empty($row['warranties_count']) ||
            !empty($row['active_warranties_count']) ||
            !empty($row['jobs_count']) ||
            !empty($row['expenses']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach ([
                'asset_name',
                'item_name',
                'warranties_count',
                'active_warranties_count',
                'jobs_count',
                'expenses',
            ] as $field) {
                if (!isset($row[$field]) || $row[$field] === null || $row[$field] === '') {
                    $missingFieldsRows[] = $index + 1;
                    break; // No need to check other fields for this row
                }
            }
        }

        if (!empty($missingFieldsRows)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Asset List and Expenses\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }

        // Proceed with further processing

        foreach ($filteredRows as $row) {
            if ($row['asset_name'] != null) {
                // Check if asset already exists
                if (!isset($this->data[$row['asset_name']])) {
                    $this->data[$row['asset_name']] = [
                        'name'  => $row['asset_name'],
                        'items' => [],
                    ];
                }

                // Append item to asset
                $this->data[$row['asset_name']]['items'][] = [
                    'name'                    => $row['item_name'],
                    'asset_code'              => (string) $row['asset_code'] ?? null,
                    'location'                => $row['location'] ?? null, // Assuming there might be empty locations
                    'warranties_count' => $row['warranties_count'],
                    'active_warranties_count' => $row['active_warranties_count'],
                    'jobs_count'              => $row['jobs_count'],
                    'expenses'                => $row['expenses'],
                ];
            }
        }

        $this->data = array_values($this->data);
    }

    public function getResults(): array
    {
        // Convert the associative array into indexed array
        return array_values($this->data);
    }
}
