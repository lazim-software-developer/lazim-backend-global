<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ReserveFundImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'section',
            'service_code',
            'balance',
        ];

        // Check if the file is empty
        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Reserve Fund Statement\nYou have uploaded an empty file")
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
                ->body("File Field: Reserve Fund Statement\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            throw new Exception();
        }

        $filteredRows = $rows->filter(function($row) {
            return !empty($row['section']) || !empty($row['service_code']) || !empty($row['balance']);
        });
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach (['section', 'service_code', 'balance'] as $field) {
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
                ->body("File Field: Reserve Fund Statement\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            throw new Exception();
        }

        // Proceed with further processing

        foreach ($filteredRows as $row) {
            if ($row['section'] === 'income') {
                $this->data['income'][] = [
                    'service_code' => $row['service_code'],
                    'balance' => $row['balance'],
                ];
            } elseif ($row['section'] === 'expense') {
                $this->data['expense'][] = [
                    'service_code' => $row['service_code'],
                    'balance' => $row['balance'],
                ];
            }
        }
    }
}
