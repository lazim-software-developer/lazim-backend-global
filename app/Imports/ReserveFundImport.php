<?php

namespace App\Imports;

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
            'date', 'description',
           'debit_amount',
           'credit_amount',
           ];
   
           // Extract the headings from the first row
           $extractedHeadings = array_keys($rows->first()->toArray());
   
           // Check if all expected headings are present in the extracted headings
           $missingHeadings = array_diff($expectedHeadings, $extractedHeadings);
   
           if (!empty($missingHeadings)) {
               Notification::make()
                   ->title("Upload valid excel file.")
                   ->danger()
                   ->body("Missing headings: " . implode(', ', $missingHeadings))
                   ->send();
               return 'failure';
           } else {
            foreach ($rows as $row) {
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
            Notification::make()
                ->title("Details uploaded successfully")
                ->success()
                ->send();
            return 'success';
        }
    }
}
