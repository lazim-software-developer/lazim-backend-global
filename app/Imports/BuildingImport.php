<?php

namespace App\Imports;

use App\Models\Building\Building;
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

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        // Define the expected headings
        $expectedHeadings = ['name', 'property_group_id', 'address_line1', 'area'];

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
                $building = Building::create([
                    'name' => $row['name'],
                    'property_group_id' => $row['property_group_id'],
                    'address_line1' => $row['address_line1'],
                    'area' => $row['area'],
                    'owner_association_id' => $this->oaId,
                    'show_inhouse_services' => 0,
                    'managed_by' => 'Property Manager',
                ]);

                // Sync the relationship with OwnerAssociation with pivot data
                $building->ownerAssociations()->sync([
                    $this->oaId => [
                        'from' => now(),
                        'to'   => null, // or set an appropriate date
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
