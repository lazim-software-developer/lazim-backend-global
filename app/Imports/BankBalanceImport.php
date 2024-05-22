<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BankBalanceImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'type',
            'opening_credit',
            'opening_debit',
            'opening_balance',
            'credit',
            'debit',
            'balance',
            'closing_credit',
            'closing_debit',
            'closing_balance',
            'unidentified_credit',
            'unidentified_debit',
            'post_dated_credit',
            'post_dated_debit',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Bank Balance\nYou have uploaded an empty file")
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
                ->body("File Field: Bank Balance\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach ([
                'type', 
                'opening_credit', 
                'opening_debit', 
                'opening_balance', 
                'credit', 
                'debit', 
                'balance', 
                'closing_credit', 
                'closing_debit', 
                'closing_balance', 
                'unidentified_credit', 
                'unidentified_debit', 
                'post_dated_credit', 
                'post_dated_debit'
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
                ->body("File Field: Bank Balance\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) 
        {
            if($row['type'] != null) {
                $sectionType = $row['type'];
    
                $this->data[$sectionType] = [
                    'opening_credit'      => $row['opening_credit'],
                    'opening_debit'       => $row['opening_debit'],
                    'opening_balance'     => $row['opening_balance'],
                    'credit'              => $row['credit'],
                    'debit'               => $row['debit'],
                    'balance'             => $row['balance'],
                    'closing_credit'      => $row['closing_credit'],
                    'closing_debit'       => $row['closing_debit'],
                    'closing_balance'     => $row['closing_balance'],
                    'unidentified_credit' => $row['unidentified_credit'],
                    'unidentified_debit'  => $row['unidentified_debit'],
                    'post_dated_credit'   => $row['post_dated_credit'],
                    'post_dated_debit'    => $row['post_dated_debit'],
                ];
            }
        }
    }
}
