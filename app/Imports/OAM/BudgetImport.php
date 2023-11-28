<?php

namespace App\Imports\OAM;

use App\Models\Accounting\Budget;
use App\Models\Accounting\Budgetitem;
use App\Models\Master\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\ValidationException;

class BudgetImport implements ToCollection, WithHeadingRow
{
    protected $budgetPeriod;
    protected $buildingId;

    public function __construct($budgetPeriod, $buildingId)
    {
        $this->budgetPeriod = $budgetPeriod;
        $this->buildingId = $buildingId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        [$start, $end] = explode(' - ', $this->budgetPeriod);
        $startDate = Carbon::createFromFormat('M Y', $start)->startOfMonth();
        $endDate = Carbon::createFromFormat('M Y', $end)->endOfMonth();

        // Check if budget exists for the given period
        $existingBudget = Budget::where('building_id', $this->buildingId)
            ->where('budget_from', $startDate->toDateString())
            ->where('budget_to', $endDate->toDateString())
            ->first();

        if ($existingBudget) {
            // Throw an error message
            throw ValidationException::withMessages([
                'budget' => 'A budget for the specified period and building already exists.'
            ]);
        }

        $budget = Budget::create([
            'building_id' => $this->buildingId,
            'owner_association_id' => auth()->user()->owner_association_id,
            'budget_period' => $this->budgetPeriod,
            'budget_from' => $startDate->toDateString(),
            'budget_to' => $endDate->toDateString(),
        ]);

        foreach ($rows as $row) {
            $service = Service::where('code', $row['servicecode'])->first();
            if ($service) {
                Budgetitem::create([
                    'budget_id' => $budget->id,
                    'service_id' => $service->id,
                    'budget_excl_vat' => $row['budget'],
                    'vat_rate' => 0.05,
                    'vat_amount' => $row['budgetvat'],
                    'total' => $row['budget'] + $row['budgetvat'],
                ]);
            }
        }
    }
}
