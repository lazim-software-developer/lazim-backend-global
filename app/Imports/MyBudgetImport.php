<?php

namespace App\Imports;

use App\Models\Accounting\SubCategory;
use Carbon\Carbon;
use App\Models\Master\Service;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Illuminate\Support\Collection;
use App\Models\Accounting\Budgetitem;
use App\Models\Accounting\Budgetitems;
use App\Models\Accounting\Category;
use Maatwebsite\Excel\Concerns\ToCollection;

class MyBudgetImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $buildingId = Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id')->first();
        
        $budgetId = Budget::create([
            'building_id' => $buildingId,
            'owner_association_id' => auth()->user()->owner_association_id,
            'budget_period' => 'Jan1-Dec31',
            'budget_from' => now()->toDateString(),
            'budget_to' => now()->addYear()->toDateString(),
        ]);
        // dd($budgetId);
        $iterate = count($collection);
        $int = 1;
        while ($iterate > 1) {
            $categoryId = Category::where('name',$collection[$int][4])->pluck('id')->first();
            $subcatetoryId = SubCategory::where('name',$collection[$int][5])->where('category_id',$categoryId)->pluck('id')->first();
            $serviceId = Service::where('name',$collection[$int][1])->pluck('id')->first();
            Budgetitem::create([
                'budget_id' => $budgetId->id,
                'service_id' => $serviceId,
                'budget_excl_vat' => $collection[$int][2],
                'vat_rate' => 0.05,
                'vat_amount' => $collection[$int][3],
                'total' => $collection[$int][2]+$collection[$int][3],
            ]);
            $int = $int + 1;
            $iterate = $iterate - 1;
        }
    }
}
