<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class HappinessCenterImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'happiness_center_id',
            'open',
            'resolved',
            'total',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Happiness Center\nYou have uploaded an empty file")
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
                ->body("File Field: Happiness Center\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach (['happiness_center_id', 'open', 'resolved', 'total'] as $field) {
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
                ->body("File Field: Happiness Center\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) 
        {
            if($row['happiness_center_id'] && $row['open'] !=='' && $row['resolved'] !=='' && $row['total'] !=='') {
                $this->data[] = [
                    'happiness_center_id'  => $row['happiness_center_id'],
                    'open' => $row['open'],
                    'resolved' => $row['resolved'],
                    'total' => $row['total'],
                ];
            }
        }
    }
}
