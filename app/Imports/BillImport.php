<?php

namespace App\Imports;

use App\Models\Bill;
use App\Models\Building\Flat;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BillImport implements ToCollection, WithHeadingRow
{
    protected $buildingId;
    protected $month;
    protected $type;

    private $fieldMappings = [
        'unit_number' => 'Unit Number',
        'amount'      => 'Amount',
        'due_date'    => 'Due Date',
        'status'      => 'Status',
        'bill_number' => 'Bill Number',
    ];

    public function __construct($buildingId, $month, $type)
    {
        $this->buildingId = $buildingId;
        $this->month      = $month;
        $this->type       = $type;
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
        // Convert the expected headings to snake_case for Excel import
        $expectedHeadings = ['unit_number', 'amount', 'due_date', 'status', 'bill_number'];
        $validStatuses    = ['Pending', 'Paid', 'Overdue'];
        $recordsImported  = 0;
        $invalidRows      = [];

        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload failed")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Debug the actual headers from Excel
        \Log::info('Excel Headers:', array_keys($rows->first()->toArray()));

        $extractedHeadings = array_keys($rows->first()->toArray());
        $missingHeadings   = array_diff($expectedHeadings, $extractedHeadings);

        if (!empty($missingHeadings)) {
            $excelHeadings = array_map(function ($heading) {
                return $this->fieldMappings[$heading] ?? $heading;
            }, $missingHeadings);

            Notification::make()
                ->title("Upload failed")
                ->danger()
                ->body("Missing columns in Excel: " . implode(', ', $excelHeadings))
                ->send();
            return 'failure';
        }

        foreach ($rows as $index => $row) {
            $rowNumber        = $index + 2; // Excel row number (accounting for header)
            $validationErrors = [];

            // Access columns using snake_case keys
            if (!isset($row['unit_number']) || trim($row['unit_number']) === '') {
                $validationErrors[] = "Unit Number cannot be empty";
            }
            if (!isset($row['bill_number']) || trim($row['bill_number']) === '') {
                $validationErrors[] = "Bill Number cannot be empty";
            } elseif (strlen($row['bill_number']) < 4 || strlen($row['bill_number']) > 15) {
                $validationErrors[] = "Bill Number must be between 4 and 15 characters";
            }

            if (!isset($row['amount']) || trim($row['amount']) === '') {
                $validationErrors[] = "Amount cannot be empty";
            }
            if (!isset($row['due_date']) || trim($row['due_date']) === '') {
                $validationErrors[] = "Due Date cannot be empty";
            }
            if (!isset($row['status']) || trim($row['status']) === '') {
                $validationErrors[] = "Status cannot be empty";
            }

            if (!empty($validationErrors)) {
                $invalidRows[] = [
                    'row'   => $rowNumber,
                    'error' => "Row $rowNumber: " . implode(', ', $validationErrors),
                ];
                continue;
            }

            // Validate status
            $status = ucfirst(strtolower($row['status']));
            if (!in_array($status, $validStatuses, true)) {
                $invalidRows[] = [
                    'row'   => $rowNumber,
                    'error' => "Row $rowNumber: Invalid Status '{$row['status']}'. Allowed values are: " . implode(', ', $validStatuses),
                ];
                continue;
            }

            // Find flat_id using property_number
            $flat = Flat::where('property_number', $row['unit_number'])
                ->where('building_id', $this->buildingId)
                ->first();

            if (!$flat) {
                $invalidRows[] = [
                    'row'   => $rowNumber,
                    'error' => "Row $rowNumber: Unit Number '{$row['unit_number']}' not found in selected building",
                ];
                continue;
            }

            // Check for existing bill
            $existingBill = Bill::where('flat_id', $flat->id)
                ->where('type', $this->type)
                ->whereMonth('month', Carbon::parse($this->month)->month)
                ->whereYear('month', Carbon::parse($this->month)->year)
                ->first();

            if ($existingBill) {
                $invalidRows[] = [
                    'row'   => $rowNumber,
                    'error' => "Row $rowNumber: Bill already exists for Unit Number '{$row['unit_number']}' for the selected month and type",
                ];
                continue;
            }

            // Process valid row
            $dueDate = $this->convertExcelDate($row['due_date'] ?? null);

            Bill::create([
                'flat_id'           => $flat->id,
                'bill_number'       => $row['bill_number'] ?? null,
                'type'              => $this->type,
                'amount'            => $row['amount'],
                'month'             => $this->month,
                'due_date'          => $dueDate,
                'status'            => $status,
                'uploaded_by'       => Auth::id(),
                'uploaded_on'       => Carbon::now(),
                'status_updated_by' => Auth::id(),
            ]);

            $recordsImported++;
        }

        // Show validation errors if any
        if (!empty($invalidRows)) {
            $errorMessage = "Some rows were skipped due to validation errors:\n\n";
            foreach ($invalidRows as $error) {
                $errorMessage .= "{$error['error']}\n";
            }

            Notification::make()
                ->title("Partial import completed")
                ->warning()
                ->duration(10000)
                ->body($errorMessage . "\n" . ($recordsImported > 0 ? "$recordsImported records were successfully imported." : ""))
                ->send();
        }

        if ($recordsImported === 0) {
            Notification::make()
                ->title("Upload failed")
                ->danger()
                ->body("No records were imported. Please check the data and try again.")
                ->send();
            return 'failure';
        }

        if (empty($invalidRows)) {
            Notification::make()
                ->title("Bills uploaded successfully")
                ->success()
                ->body("Successfully imported {$recordsImported} records.")
                ->send();
        }

        return 'success';
    }
}
