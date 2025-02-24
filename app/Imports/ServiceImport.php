<?php

namespace App\Imports;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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

        if($rows->first()== null){
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Check if the file is empty
        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Services Requests\nYou have uploaded an empty file")
                ->send();
            // return 'failure';
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
                ->body("File Field: Services Requests\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            // return 'failure';
            throw new Exception();
        }

        $filteredRows = $rows->filter(function ($row) {
            return isset($row['eservice_id']) || isset($row['open']) || isset($row['resolved']) || isset($row['total']);
        });

        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($filteredRows as $index => $row) {
            foreach (['eservice_id', 'open', 'resolved', 'total'] as $field) {
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
                ->body("File Field: Services Requests\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            // return 'failure';
            throw new Exception();
        }

        foreach ($filteredRows as $row) {
            if ($row['eservice_id'] && $row['open'] !== '' && $row['resolved'] !== '' && $row['total'] !== '') {
                $this->data[] = [
                    'eservice_id' => $row['eservice_id'],
                    'open'        => $row['open'],
                    'resolved'    => $row['resolved'],
                    'total'       => $row['total'],
                ];
            }
        }
    }
}
