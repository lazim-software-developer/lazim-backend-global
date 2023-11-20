<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CreateInvoiceRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\InvoiceStatsResource;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\WDA;
use App\Models\Vendor\Vendor;
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

    public function store(CreateInvoiceRequest $request){

        $document = optimizeDocumentAndUpload($request->document);

        $wda=WDA::find($request->wda_id);
        $name = auth()->user()->technicianVendors()->first()->vendor->OA->name;
        $invoice_id = strtoupper(substr($name, 0, 4)).date('YmdHis');
        $request->merge([
            'building_id' => $wda->building_id,
            'contract_id' => $wda->contract_id,
            'invoice_number' =>$invoice_id,
            'document' => $document,
            'created_by' => auth()->user()->id,
            'status' => 'pending',
            'vendor_id' => auth()->user()->technicianVendors()->first()->vendor_id
        ]);
        
        $invoice =Invoice::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Invoice created successfully!',
            'code' => 201,
            'status' => 'success',
            'data' => $invoice,
        ]))->response()->setStatusCode(201);
    }

    public function stats(Request $request,Vendor $vendor){

        // Get the date filter or use the current month and year
        $dateFilter = $request->input('date', Carbon::now()->format('F Y'));

        // Parse the date filter to get the start and end of the month
        $startDate = Carbon::createFromFormat('F Y', $dateFilter)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $dateFilter)->endOfMonth();

        $invoiceQuery = Invoice::where('vendor_id', $vendor->id)
                    ->whereBetween('date', [$startDate, $endDate])->get();

        if ($request->has('building_id') && !empty($request->building_id)) {
            $invoiceQuery = $invoiceQuery->where('building_id', $request->building_id);
        }

        return new InvoiceStatsResource($invoiceQuery);
    }
}
