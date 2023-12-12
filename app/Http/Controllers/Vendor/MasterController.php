<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Tender;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterController extends Controller
{
    public function getVendorsBasedOnServices(Request $request)
    {
        // Get the selected service IDs from the request
        $serviceId = $request->input('service_id');

        // Retrieve vendors related to the service ID
        $vendors = Vendor::whereHas('services', function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId); // Specify the table name here
        })->get();

        // Return the view with the vendors
        return view('partials.vendors-list', compact('vendors'));
    }

    public function getAvailableServices(Budget $budget, $subcategory)
    {
        // Fetch service IDs for which tenders have already been created for this budget
        $existingTenderServiceIds = Tender::where('budget_id', $budget->id)->pluck('service_id');

        // Start building the query for available services
        $query = Service::whereNotIn('id', $existingTenderServiceIds);

        $availableServices =$query->where('subcategory_id', $subcategory)->get();

        return response()->json($availableServices);
    }
}
