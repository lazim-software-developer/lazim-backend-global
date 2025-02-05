<?php

namespace App\Imports;

use App\Models\Building\Building;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BuildingImport implements ToCollection, WithHeadingRow
{

    public function __construct(protected $oaId)
    {
        //
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

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // Update expected headings
        $expectedHeadings = [
            'name',
            'building_type',
            'property_group_id',
            'address_line1',
            'area',
            'floors',
            'parking_count',
            'contract_start_date',
            'contract_end_date',
        ];

        if ($rows->first() == null) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }

        // Extract the headings from the first row
        if ($rows->first()->filter()->isEmpty()) {
            Notification::make()
                ->title("Upload valid excel file.")
                ->danger()
                ->body("You have uploaded an empty file")
                ->send();
            return 'failure';
        }
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
        }

        $notImported = [];
        foreach ($rows as $index => $row) {
            $errors = [];

            // Basic required field validations
            if (empty($row['name'])) {
                $errors[] = 'Name is missing';
            }
            if (empty($row['property_group_id'])) {
                $errors[] = 'Property Group ID is missing';
            }
            if (empty($row['address_line1'])) {
                $errors[] = 'Address Line 1 is missing';
            }

            // Enhanced date validations with better error messages
            if (empty($row['contract_start_date'])) {
                $errors[] = 'Contract Start Date is required';
            } elseif (!$this->convertExcelDate($row['contract_start_date'])) {
                $errors[] = 'Invalid Contract Start Date format (use YYYY-MM-DD)';
            }

            if (empty($row['contract_end_date'])) {
                $errors[] = 'Contract End Date is required';
            } elseif (!$this->convertExcelDate($row['contract_end_date'])) {
                $errors[] = 'Invalid Contract End Date format (use YYYY-MM-DD)';
            }

            // Only check date comparison if both dates are valid
            if (!empty($row['contract_start_date']) &&
                !empty($row['contract_end_date']) &&
                $this->convertExcelDate($row['contract_start_date']) &&
                $this->convertExcelDate($row['contract_end_date'])) {

                $fromDate = $this->convertExcelDate($row['contract_start_date']);
                $toDate   = $this->convertExcelDate($row['contract_end_date']);

                if ($toDate <= $fromDate) {
                    $errors[] = 'Contract End Date must be after Contract Start Date';
                }
            }

            // Other field validations
            if (!empty($row['area']) && !is_numeric($row['area'])) {
                $errors[] = 'Invalid Area format';
            }
            if (!empty($row['floors']) && !is_numeric($row['floors'])) {
                $errors[] = 'Invalid Floors format';
            }

            // Duplicate checks
            if (!empty($row['name']) && Building::where('name', $row['name'])->exists()) {
                $errors[] = 'Building name already exists';
            }
            if (!empty($row['property_group_id']) && Building::where('property_group_id', $row['property_group_id'])->exists()) {
                $errors[] = 'Property Group ID already exists';
            }

            if (!empty($errors)) {
                $rowIdentifier = $row['name'] ?? 'Row #' . ($index + 2);
                $notImported[] = "{$rowIdentifier}: " . implode(', ', $errors);
                continue;
            }

            // Create building if no errors
            // Map the new column names to the database fields
            $fromDate = $this->convertExcelDate($row['contract_start_date']);
            $toDate   = $this->convertExcelDate($row['contract_end_date']);

            $building = Building::create([
                'name'                  => $row['name'],
                'building_type'         => $row['building_type'],
                'property_group_id'     => $row['property_group_id'],
                'address_line1'         => $row['address_line1'],
                'area'                  => $row['area'],
                'floors'                => $row['floors'],
                'parking_count'         => $row['parking_count'],
                'from'                  => $fromDate ?: null,
                'to'                    => $toDate ?: null,
                'owner_association_id'  => $this->oaId,
                'show_inhouse_services' => 0,
                'managed_by'            => 'Property Manager',
            ]);

            // Sync the relationship with OwnerAssociation with pivot data
            $building->ownerAssociations()->sync([
                $this->oaId => [
                    'from'   => $fromDate ?: null,
                    'to'     => $toDate ?: null,
                    'active' => true,
                ],
            ]);
        }
        if (!empty($notImported)) {
            Notification::make()
                ->title("Failed to import some buildings")
                ->body(implode("\n", $notImported))
                ->danger()
                ->send();

            return 'failure';
        } else {
            Notification::make()
                ->title("Buildings imported successfully.")
                ->success()
                ->send();

            return 'success';
        }
    }
}
