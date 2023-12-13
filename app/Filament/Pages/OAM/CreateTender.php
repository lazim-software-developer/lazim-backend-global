<?php

namespace App\Filament\Pages\OAM;

use App\Jobs\OAM\SendProposalRequestEmail;
use App\Models\Accounting\Budget;
use App\Models\Accounting\SubCategory;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $buildingId = $this->budget->building_id;

        $serviceIds = $this->budget->tenders()
            ->pluck('service_id')
            ->unique();

        $building = Building::with(['services.subcategory'])
            ->where('id', $buildingId)
            ->first();

        $services = Service::whereHas('buildings', function ($query) use ($buildingId) {
            $query->where('buildings.id', $buildingId); // Specify the table name
        })->get();

        // Get the unique subcategories for these services
        $subcategories = $services->whereNotNull('subcategory')->pluck('subcategory')->unique('id');

        return [
            'subcategories' => $subcategories,
            'building' => $building,
            'budgetId' => $this->budget->id,
            'serviceIds' => $serviceIds
        ];
    }

    public function store(Request $request, Budget $budget)
    {
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
            'document' => $documentUrl,
            'service_id' => $request->get('service'),
            'tender_type' => $request->get('tender_type')
        ]);

        // Attach tender vendors
        $tender->vendors()->syncWithoutDetaching($request->get('vendors'));
        if ($request->get('vendors') != null) {
            // Send email to vendors
            $vendors = Vendor::whereIn('id', $request->get('vendors'))->get();
            SendProposalRequestEmail::dispatch($vendors, $documentUrl);

            Notification::make()
                ->title("Tender created successfully")
                ->success()
                ->send();

            return redirect('/admin/tenders');
        } else {
            Notification::make()
                ->title("There Were No Vendor For Selected Service")
                ->danger()
                ->send();
            return redirect()->back();
        }


    }
}
