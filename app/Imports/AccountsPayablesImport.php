<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AccountsPayablesImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {   
        $expectedHeadings = [
            'service_code',
            'account_name',
            'bill',
            'payment',
            'opening_balance',
            'closing_balance',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Accounts Payables\nYou have uploaded an empty file")
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
                ->body("File Field: Accounts Payables\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        $filteredRows = $rows->filter(function($row) {
            return !empty($row['service_code']) || !empty($row['account_name']) || !empty($row['bill']) || !empty($row['payment']) || !empty($row['opening_balance']) || !empty($row['closing_balance']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach (['service_code', 'account_name', 'bill', 'payment', 'opening_balance', 'closing_balance'] as $field) {
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
                ->body("File Field: Accounts Payables\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        
        // Proceed with further processing
        
        foreach ($filteredRows as $row) 
        {
            if($row['account_name'] && $row['bill']  && $row['payment'] && $row['opening_balance'] && $row['closing_balance']) {
                $this->data[] = [
                    'service_code'  => $row['service_code'] ?? '',
                    'account_name' => $row['account_name'],
                    'bill'  => $row['bill'],
                    'payment' => $row['payment'],
                    'opening_balance' => $row['opening_balance'],
                    'closing_balance' => $row['closing_balance'],
                ];
            }
        }
    }
}
