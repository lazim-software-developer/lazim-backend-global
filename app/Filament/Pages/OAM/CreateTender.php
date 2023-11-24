<?php

namespace App\Filament\Pages\OAM;

use App\Jobs\OAM\SendProposalRequestEmail;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\Vendor\Vendor;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class CreateTender extends Page
{
    public $budget;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.oam.create-tender';

    protected static ?string $slug = '{budget}/tender/create';

    public function mount(Budget $budget) // Type-hint the Building model
    {
        $this->budget = $budget;
    }

    protected function getViewData(): array
    {
        $buildingId = $this->budget->building_id; // Your building ID

        $building = Building::with(['services.subcategory'])
            ->where('id', $buildingId)
            ->firstOrFail();

        if ($building) {
            // Group services by subcategory
            $groupedServices = $building->services
                ->groupBy(function ($service) {
                    return $service->subcategory->name; // Group by subcategory name
                });
        } else {
            $groupedServices = collect();
        }
        $subcategoryServices = [];

        foreach ($groupedServices as $subcategoryName => $services) {
            $subcategoryServices[] = [
                'subcategory_name' => $subcategoryName,
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                    ];
                })->toArray()
            ];
        }

        return [
            'subcategoryServices' => $subcategoryServices,
            'building' => $building,
            'budgetId' => $this->budget->id
        ];
    }

    public function store(Request $request, Budget $budget) {
        $building = Building::where('id', $budget->building_id)->first();
        // Upload document to S3
        $documentUrl = optimizeDocumentAndUpload($request->document, 'dev');

        $tender = Tender::create([
            'date' => now(),
            'created_by' => auth()->user()->id,
            'building_id' => $building->id,
            'budget_id' => $budget->id,
            'owner_association_id' => $building->owner_association_id,
            'end_date' => $request->get('end_date'),
            'document' => $documentUrl
        ]);

        // Attach tender vendors
        $tender->vendors()->syncWithoutDetaching($request->get('vendors'));

        // Attach tender services
        $tender->services()->syncWithoutDetaching($request->get('services'));

        // Send email to vendors
        $vendors = Vendor::whereIn('id', $request->get('vendors'))->get();
        SendProposalRequestEmail::dispatch($vendors, $documentUrl);

        return redirect()->back();
    }
}
