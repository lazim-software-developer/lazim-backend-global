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
        $selectedServiceIds = $request->input('selectedServices', []);

        // Query the vendors who offer all the selected services
        $vendors = Vendor::whereHas('services', function ($query) use ($selectedServiceIds) {
            $query->whereIn('service_id', $selectedServiceIds);
        }, '=', count($selectedServiceIds))->get();

        // Return the view with the vendors
        return view('partials.vendors-list', compact('vendors'));
    }
}
