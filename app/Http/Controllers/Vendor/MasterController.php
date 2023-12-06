<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

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
}
