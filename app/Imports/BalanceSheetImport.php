<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BalanceSheetImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'section',
            'name',
            'balance',
        ];

        // Check if the file is empty
        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Balance Sheet\nYou have uploaded an empty file")
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
                ->body("File Field: Balance Sheet\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }

        $filteredRows = $rows->filter(function($row) {
            return !empty($row['section']) || !empty($row['name']) || !empty($row['balance']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach (['section', 'name', 'balance'] as $field) {
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
                ->body("File Field: Balance Sheet\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }

        // Proceed with further processing

        foreach ($filteredRows as $row)
        {
            if ($row['section'] === 'income') {
                $this->data['income'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                    'code' => $row['code'],
                ];
            } elseif ($row['section'] === 'expense') {
                $this->data['expense'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'asset') {
                $this->data['asset'][] = [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'liability') {
                $this->data['liability'] []= [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }elseif ($row['section'] === 'equity') {
                $this->data['equity'] []= [
                    'name' => $row['name'],
                    'balance' => $row['balance'],
                ];
            }
        }
    }
}
