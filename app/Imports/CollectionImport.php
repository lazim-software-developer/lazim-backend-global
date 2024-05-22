<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class CollectionImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'section',
            'utility_reference',
            'amount',
            'utility_name',
            'provider_name',
            'duration',
            'duration_str',
            'trend_amount',
        ];
        
        // Define the required fields for each section type
        $sectionRequiredFields = [
            'by_method' => ['payment_method_id', 'amount'],
            'recovery' => ['opening_balanace', 'charge', 'payment', 'closing_balance', 'rate'],
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Collection Report\nYou have uploaded an empty file")
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
                ->body("File Field: Collection Report\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        $filteredRows = $rows->filter(function($row) {
            return !empty($row['section']) || 
                   !empty($row['utility_reference']) || 
                   !empty($row['amount']) || 
                   !empty($row['utility_name']) || 
                   !empty($row['provider_name']) || 
                   !empty($row['duration']) || 
                   !empty($row['duration_str']) || 
                   !empty($row['trend_amount']);
        });
        
        // Check for missing required fields in rows based on the section type
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            $section = $row['section'];
            if (isset($sectionRequiredFields[$section])) {
                foreach ($sectionRequiredFields[$section] as $field) {
                    if (empty($row[$field])) {
                        $missingFieldsRows[] = $index + 1;
                        break; // No need to check other fields for this row
                    }
                }
            }
        }
        
        if (!empty($missingFieldsRows)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Collection Report\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        foreach ($filteredRows as $row) 
        {
            if ($row['section'] === 'by_method') {
                $this->data['by_method'][] = [
                    'payment_method_id' => $row['payment_method_id'],
                    'amount'            => $row['amount'],
                ];
            } elseif ($row['section'] === 'recovery') {
                $this->data['recovery'] = [
                    'opening_balanace'  => $row['opening_balanace'],
                    'charge'            => $row['charge'],
                    'payment'           => $row['payment'],
                    'closing_balance'   => $row['closing_balance'],
                    'rate'              => $row['rate']
                ];
            }
        }
    }
}
