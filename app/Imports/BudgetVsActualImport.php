<?php

namespace App\Imports;

use Filament\Notifications\Notification;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BudgetVsActualImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $expectedHeadings = [
            'section',
            'actual',
            'budget',
            'variance',
            'service_code',
        ];
        
        // Check if the file is empty
        if ($rows->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("File Field: Budget Vs Actual\nYou have uploaded an empty file")
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
                ->body("File Field: Budget Vs Actual\nMissing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }
        
        // Check for missing required fields in rows
        $missingFieldsRows = [];
        foreach ($rows as $index => $row) {
            foreach (['section', 'actual', 'budget', 'variance', 'service_code'] as $field) {
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
                ->body("File Field: Budget Vs Actual\nRequired fields are missing in the following row(s): " . implode(', ', $missingFieldsRows))
                ->send();
            return 'failure';
        }
        
        // Proceed with further processing
        
        foreach ($rows as $row) {
            if (($row['section']) === 'income_accounts') {
                $this->data['income_accounts'][] = [
                    'actual' => $row['actual'],
                    'budget' => $row['budget'],
                    'variance' => $row['variance'],
                    'service_code' => $row['service_code'],
                ];
            }else if ($row['section'] === 'expense_accounts') {
                $this->data['expense_accounts'][] = [
                    'actual' => $row['actual'],
                    'budget' => $row['budget'],
                    'variance' => $row['variance'],
                    'service_code' => $row['service_code'],
                ];
            }
        }
    
    }
}
