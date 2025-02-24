<?php

namespace App\Imports\OAM;

use App\Models\Accounting\Budget;
use App\Models\Accounting\Budgetitem;
use App\Models\Building\Building;
use App\Models\Master\Service;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BudgetImport implements ToCollection, WithHeadingRow
{
    protected $budgetPeriod;
    protected $buildingId;

    public function __construct($budgetPeriod, $buildingId)
    {
        $this->budgetPeriod = $budgetPeriod;
        $this->buildingId   = $buildingId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // Define the expected headings
        $expectedHeadings = ['servicecode', 'servicename', 'budget', 'budgetvat', 'category', 'subcategory'];

        if($rows->first()== null){
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
        [$start, $end] = explode(' - ', $this->budgetPeriod);
        $startDate     = Carbon::createFromFormat('M Y', $start)->startOfMonth();
        $endDate       = Carbon::createFromFormat('M Y', $end)->endOfMonth();

        $building = Building::where('id', $this->buildingId)->first();

        // Check if budget exists for the given period
        $existingBudget = Budget::where('building_id', $this->buildingId)
            ->where('budget_from', $startDate->toDateString())
            ->where('budget_to', $endDate->toDateString())
            ->first();

        if ($existingBudget) {
            // Create a Laravel ValidationException
            $validator = Validator::make([], []); // Empty data and rules
            $validator->errors()->add('budget', 'A budget for the specified period and building already exists.');
            Notification::make()
                ->title("A budget for the specified period and building already exists. ")
                ->danger()
                ->send();
            return 'error';
        }

        $budget = Budget::create([
            'building_id'          => $this->buildingId,
            'owner_association_id' => Filament::getTenant()?->id ?? auth()->user()?->owner_association_id,
            'budget_period'        => $this->budgetPeriod,
            'budget_from'          => $startDate->toDateString(),
            'budget_to'            => $endDate->toDateString(),
        ]);

        foreach ($rows as $row) {
            // $category = Category::firstOrCreate(
            //     [
            //         'name' => $row['category'],
            //     ],
            //     [
            //         'code' => preg_replace("/[^a-zA-Z]/", "", $row['servicecode']),
            //     ]
            // );
            // $subcategory = SubCategory::firstOrCreate(
            //     [
            //         'name' => $row['subcategory'],
            //     ],
            //     [
            //         'category_id' => $category->id,
            //         'code' => 'Y',
            //     ]
            //     );
            // $service = Service::firstOrCreate(
            //     [
            //         'code' => $row['servicecode'],
            //         'subcategory_id' => $subcategory->id,
            //     ],
            //     [
            //         'name' => $row['servicename'],
            //         'type' => 'vendor_service',
            //         'active' => true,
            //     ]
            // );

            $service = Service::where('code', $row['servicecode'])->first();

            // Check if the service is found, if not, move on to the next iteration
            if (!$service) {
                continue;
            }

            // Check if the building has this service, if not add the service to building
            $building->services()->syncWithoutDetaching([$service->id]);

            if ($service) {
                Budgetitem::create([
                    'budget_id'       => $budget->id,
                    'service_id'      => $service->id,
                    'budget_excl_vat' => $row['budget'],
                    'vat_rate'        => 0.05,
                    'vat_amount'      => $row['budgetvat'],
                    'total'           => $row['budget'] + $row['budgetvat'],
                ]);
            }
        }
        Notification::make()
            ->title("Budget imported successfully.")
            ->success()
            ->send();

        return 'success';
    }
}
