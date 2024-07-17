<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
        if ($rows->first()->filter()->isEmpty()) {
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

        $filteredRows = $rows->filter(function ($row) {
            return !empty($row['type']) ||
            !empty($row['opening_credit']) ||
            !empty($row['opening_debit']) ||
            !empty($row['opening_balance']) ||
            !empty($row['credit']) ||
            !empty($row['debit']) ||
            !empty($row['balance']) ||
            !empty($row['closing_credit']) ||
            !empty($row['closing_debit']) ||
            !empty($row['closing_balance']) ||
            !empty($row['unidentified_credit']) ||
            !empty($row['unidentified_debit']) ||
            !empty($row['post_dated_credit']) ||
            !empty($row['post_dated_debit']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];

if (is_array($filteredRows) && !empty($filteredRows)) {
    // Iterate through each filtered row
    foreach ($filteredRows as $index => $row) {
        // List of fields to check
        $fieldsToCheck = [
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

        // Iterate through each field to check
        foreach ($fieldsToCheck as $field) {
            // Check if the field is missing, null, or empty
            if (!isset($row[$field]) || $row[$field] === null || $row[$field] === '') {
                // Add the row index (plus one) to the missingFieldsRows array
                $missingFieldsRows[] = $index + 1;
                // No need to check other fields for this row
                break;
            }
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

        foreach ($filteredRows as $row) {
            if ($row['type'] != null) {
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
