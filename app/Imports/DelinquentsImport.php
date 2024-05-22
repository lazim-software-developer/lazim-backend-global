<?php

namespace App\Imports;

use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class DelinquentsImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'unit_number',
            'recovery_notes_count',
            'unit_balance',
            'first_quarter',
            'second_quarter',
            'third_quarter',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Delinquent Owners\nYou have uploaded an empty file")
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
                ->body("File Field: Delinquent Owners\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach (['unit_number', 'recovery_notes_count', 'unit_balance', 'first_quarter', 'second_quarter', 'third_quarter'] as $field) {
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
                ->body("File Field: Delinquent Owners\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            return 'failure';
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) 
        {
            if(isset($row['unit_number']) && isset($row['recovery_notes_count']) && isset($row['unit_balance']) && isset($row['first_quarter']) && isset($row['second_quarter']) && isset($row['third_quarter'])) {
                $this->data[] = [
                    'unit_number'  => (string)$row['unit_number'],
                    'recovery_notes_count' => $row['recovery_notes_count'],
                    'unit_balance' => $row['unit_balance'],
                    'first_quarter' => $row['first_quarter'],
                    'second_quarter' => $row['second_quarter'],
                    'third_quarter' => $row['third_quarter'],
                ];
            }
        }
    }
}
