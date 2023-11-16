<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Accounting\WDA;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        // Get the date filter or use the current month and year
        $dateFilter = $request->input('date', Carbon::now()->format('F Y'));

        // Parse the date filter to get the start and end of the month
        $startDate = Carbon::createFromFormat('F Y', $dateFilter)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $dateFilter)->endOfMonth();

        // Start building the query
        $query = WDA::with('invoices')
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate]);

        // Check if building_id is provided and filter accordingly
        if ($request->has('building_id') && !empty($request->building_id)) {
            $query->where('building_id', $request->building_id);
        }

        // Execute the query and map the results
        $approvedWDAs = $query->get()->map(function ($wda) {
            // Check if an invoice has been submitted for this WDA
            $invoiceSubmitted = $wda->invoices->isNotEmpty();
            return [
                'wda_id' => $wda->id,
                'date' => $wda->date->format('Y-m-d'),
                'status' => $invoiceSubmitted ? 'Invoice Submitted' : 'Submit Invoice',
            ];
        });

        return response()->json($approvedWDAs);
    }
}
