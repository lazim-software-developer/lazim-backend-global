<?php

namespace App\Imports;

use App\Models\Bill;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BillImport implements ToCollection, WithHeadingRow
{
    protected $buildingId;
    protected $flatId;
    protected $month;

    public function __construct($buildingId, $flatId, $month)
    {
        $this->buildingId = $buildingId;
        $this->flatId     = $flatId;
        $this->month      = $month;
    }

    private function convertExcelDate($excelDate)
    {
        if (!$excelDate) {
            return null;
        }

        // Check if it's already a valid date string
        if (strtotime($excelDate) !== false) {
            return date('Y-m-d', strtotime($excelDate));
        }

        // Handle Excel number date format
        if (is_numeric($excelDate)) {
            try {
                // Excel dates are number of days since 1900-01-01 (or 1904-01-01)
                // PHP function assumes 1900 as base year
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
    public function collection(Collection $rows)
    {
        $expectedHeadings = ['type', 'amount', 'due_date', 'status'];

        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        $extractedHeadings = array_keys($rows->first()->toArray());
        $missingHeadings   = array_diff($expectedHeadings, $extractedHeadings);

        if (!empty($missingHeadings)) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("Missing headings: " . implode(', ', $missingHeadings))
                ->send();
            return 'failure';
        }

        foreach ($rows as $row) {
            if (!in_array($row['status'], ['Pending', 'Paid', 'Overdue'])) {
                continue;
            }
            $dueDate = $this->convertExcelDate($row['due_date']);

            Bill::create([
                'flat_id'           => $this->flatId,
                'type'              => $row['type'],
                'amount'            => $row['amount'],
                'month'             => $this->month,
                'due_date'          => $dueDate,
                'status'            => $row['status'],
                'uploaded_by'       => Auth::id(),
                'uploaded_on'       => Carbon::now(),
                'status_updated_by' => Auth::id(),
            ]);
        }

        Notification::make()
            ->title("Bills uploaded successfully")
            ->success()
            ->send();
        return 'success';
    }
}
