<?php

namespace App\Imports;

use App\Models\Building\Building;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Notifications\Notification;


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
        // Define the expected headings
        $expectedHeadings = ['name', 'building_type', 'property_group_id', 'address_line1', 'area', 'floors', 'parking_count', 'from', 'to'];

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
        foreach ($rows as $row) {
            $exists = Building::where('property_group_id', $row['property_group_id'])->exists();
            if ($exists) {
                $notImported[] = $row['name'];
            } else {
                $fromDate = $this->convertExcelDate($row['from']);
                $toDate   = $this->convertExcelDate($row['to']);


                $building = Building::create([
                    'name' => $row['name'],
                    'building_type' => $row['building_type'],
                    'property_group_id' => $row['property_group_id'],
                    'address_line1' => $row['address_line1'],
                    'area' => $row['area'],
                    'floors' => $row['floors'],
                    'parking_count' => $row['parking_count'],
                    'from' => $fromDate ?: null,
                    'to' => $toDate ?: null,
                    'owner_association_id' => $this->oaId,
                    'show_inhouse_services' => 0,
                    'managed_by' => 'Property Manager',
                ]);

                // Sync the relationship with OwnerAssociation with pivot data
                $building->ownerAssociations()->sync([
                    $this->oaId => [
                        'from' => $fromDate ?: null,
                        'to' => $toDate ?: null,
                        'active' => true,
                    ],
                ]);
            }
        }
        if (! empty($notImported)) {
            Notification::make()
                ->title("Buildings imported successfully.")
                ->body('Not imported Buildings' . implode(', ', $notImported))
                ->success()
                ->send();

            return 'success';
        } else {
            Notification::make()
                ->title("Buildings imported successfully.")
                ->success()
                ->send();

            return 'success';
        }
    }
}
