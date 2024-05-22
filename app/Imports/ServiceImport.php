<?php

namespace App\Imports;

use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ServiceImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'eservice_id',
            'open',
            'resolved',
            'total',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Services Requests\nYou have uploaded an empty file")
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
                ->body("File Field: Services Requests\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach (['eservice_id', 'open', 'resolved', 'total'] as $field) {
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
                ->body("File Field: Services Requests\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            return 'failure';
        }
        
        
        foreach ($rows as $row) 
        {
            if($row['eservice_id'] && $row['open'] !=='' && $row['resolved'] !=='' && $row['total'] !=='') {
                $this->data[] = [
                    'eservice_id'  => $row['eservice_id'],
                    'open' => $row['open'],
                    'resolved' => $row['resolved'],
                    'total' => $row['total'],
                ];
            }
        }
    }
}
