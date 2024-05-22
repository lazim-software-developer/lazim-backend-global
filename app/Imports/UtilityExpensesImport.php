<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UtilityExpensesImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'utility_reference',
            'amount',
            'utility_name',
            'provider_name',
            'duration',
            'duration_str',
            'trend_amount',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Utility Expenses\nYou have uploaded an empty file")
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
                ->body("File Field: Utility Expenses\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach ([
                'utility_reference', 
                'amount', 
                'utility_name', 
                'provider_name', 
                'duration', 
                'duration_str', 
                'trend_amount'
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
                ->body("File Field: Utility Expenses\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) 
        {
        $reference = $row['utility_reference'];

        if (!isset($this->data[$reference])) {
            if(isset($row['amount']) && isset($row['utility_name']) && isset($row['provider_name'])) {
                // Initialize the utility if it's not yet in our data array
                $this->data[$reference] = [
                    'utility_reference' => (string)$row['utility_reference'],
                    'amount'            => (float)$row['amount'],
                    'utility_name'      => (string)$row['utility_name'],
                    'provider_name'     => (string)$row['provider_name'],
                    'trend'             => [],
                ];
            }
        }

        // Append to the trend for the respective utility
        if(isset($row['duration']) && isset($row['duration_str']) && isset($row['trend_amount'])) {
            $this->data[$reference]['trend'][] = [
                'duration'      => (string)$row['duration'],
                'duration_str'  => (string)$row['duration_str'],
                'amount'        => (float)$row['trend_amount'],
            ];
        }
    }
    }

    public function getResults(): array
    {
        return array_values($this->data);
    }
}
