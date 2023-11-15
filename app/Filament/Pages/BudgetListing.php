<?php

namespace App\Filament\Pages;

use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Tender;
use App\Models\Master\Service;

class BudgetListing extends Page
{
    public $budget;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'budget-listing/{building}';

    protected static string $view = 'filament.pages.budget-listing';

    public function mount(Budget $budget) // Type-hint the Building model
    {
        $this->budget = $budget;
    }

    protected function getViewData(): array
    {
        // $building = Building::with('services')->find($this->budget->building_id);
        $building = Building::find(1);
        // $services = $building->services;
        $services = Service::all();

        return [
            'services' => $services,
            'building' => $building,
        ];
    }

    // Define the rules for your form
    protected function rules()
    {
        return [
            // 'endDate' => 'required|date',
            'selectedVendors' => 'required|array',
            'selectedVendors.*' => 'exists:vendors,id',
            'selectedServices' => 'required|array',
            'selectedServices.*' => 'exists:services,id',
        ];
    }

    // Define the form submission method
    public function submit()
    {
        $validatedData = $this->validate();

        DB::beginTransaction();

        try {
            $tender = Tender::create([
                'created_by' => auth()->id(),
                'date' => now(),
                'building_id' => $this->building->id,
                'budget_id' => $validatedData['budgetId'],
                'owner_association_id' => $validatedData['ownerAssociationId'],
                'end_date' => $validatedData['endDate'],
            ]);

            $tender->vendors()->attach($validatedData['selectedVendors']);
            $tender->services()->attach($validatedData['selectedServices']);

            DB::commit();

            $this->notify('success', 'Tender created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            $this->notify('danger', 'An error occurred: ' . $e->getMessage());
        }
    }
}
