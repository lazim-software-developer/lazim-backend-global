<?php

namespace App\Imports;

use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetsImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'asset_name',
            'item_name',
            'asset_code',
            'warranties_count',
            'active_warranties_count',
            'jobs_count',
            'expenses',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Asset List and Expenses\nYou have uploaded an empty file")
                ->send();
            return 'failure';
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
            return 'failure';
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach ([
                'asset_name', 
                'item_name', 
                'asset_code', 
                'warranties_count', 
                'active_warranties_count', 
                'jobs_count', 
                'expenses'
            ] as $field) {
                if (empty($row[$field])) {
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
            return 'failure';
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) 
        {
        if($row['asset_name'] != null) {
            // Check if asset already exists
            if (!isset($this->data[$row['asset_name']])) {
                $this->data[$row['asset_name']] = [
                    'name'  => $row['asset_name'],
                    'items' => []
                ];
            }

            // Append item to asset
            $this->data[$row['asset_name']]['items'][] = [
                'name'                   => $row['item_name'],
                'asset_code'             => $row['asset_code'],
                'location'               => $row['location'] ?? null, // Assuming there might be empty locations
                'warranties_count'       => $row['warranties_count'],
                'active_warranties_count'=> $row['active_warranties_count'],
                'jobs_count'             => $row['jobs_count'],
                'expenses'               => $row['expenses']
            ];
        }
    }
    }

    public function getResults(): array
    {
        // Convert the associative array into indexed array
        return array_values($this->data);
    }
}


