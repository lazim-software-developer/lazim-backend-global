<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class WorkOrdersImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'title',
            'status',
            'type',
            'total_amount',
            'vendor_name',
            'category_name',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Work Orders\nYou have uploaded an empty file")
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
                ->body("File Field: Work Orders\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        $filteredRows = $rows->filter(function($row) {
            return !empty($row['title']) || !empty($row['status']) || !empty($row['type']) || !empty($row['total_amount']) || !empty($row['vendor_name']) || !empty($row['category_name']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach (['title', 'status', 'type', 'total_amount', 'vendor_name', 'category_name'] as $field) {
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
                ->body("File Field: Work Orders\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        
        // Proceed with further processing
        
        foreach ($filteredRows as $row) 
        {
            if(isset($row['title']) && isset($row['status']) && isset($row['type']) && isset($row['total_amount']) && isset($row['vendor_name']) && isset($row['category_name'])) {
                $this->data[] = [
                    'title'  => $row['title'],
                    'status' => $row['status'],
                    'type' => $row['type'],
                    'total_amount' => $row['total_amount'],
                    'vendor_name' => $row['vendor_name'],
                    'category_name' => $row['category_name'],
                ];
            }
        }
    }
}
