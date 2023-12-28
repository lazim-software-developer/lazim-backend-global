<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CreateInvoiceRequest;
use App\Http\Requests\Vendor\InvoiceUpdateRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\InvoiceResource;
use App\Http\Resources\Vendor\InvoiceStatsResource;
use App\Http\Resources\Vendor\WdaInvoiceResource;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceAudit;
use App\Models\Accounting\WDA;
use App\Models\Building\Building;
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
                'date' => Carbon::parse($wda->date)->format('Y-m-d'),
                'status' => $invoiceSubmitted ? 'Invoice Submitted' : 'Submit Invoice',
            ];
        });
        return WdaInvoiceResource::collection($approvedWDAs);
    }

    public function store(CreateInvoiceRequest $request, Vendor $vendor)
    {

        $document = optimizeDocumentAndUpload($request->file);

        $wda = WDA::find($request->wda_id);
        $name = $vendor->OA->name;
        $invoice_id = strtoupper(substr($name, 0, 4)) . date('YmdHis');
        $request->merge([
            'building_id' => $wda->building_id,
            'contract_id' => $wda->contract_id,
            'invoice_number' => $invoice_id,
            'document' => $document,
            'created_by' => auth()->user()->id,
            'status' => 'pending',
            'vendor_id' => $vendor->id
        ]);

        $invoice = Invoice::create($request->all());
        $wda->update(['invoice_status' => 'submitted']);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Invoice created successfully!',
            'code' => 201,
            'status' => 'success',
            'data' => $invoice,
        ]))->response()->setStatusCode(201);
    }

    public function stats(Request $request, Vendor $vendor, Building $building)
    {

        // Get the date filter or use the current month and year
        $dateFilter = $request->input('date', Carbon::now()->format('F Y'));

        // Parse the date filter to get the start and end of the month
        $startDate = Carbon::createFromFormat('F Y', $dateFilter)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $dateFilter)->endOfMonth();

        $invoiceQuery = Invoice::where(['vendor_id' => $vendor->id, 'building_id' => $building->id])
            ->whereBetween('date', [$startDate, $endDate])->get();

        if ($request->has('building_id') && !empty($request->building_id)) {
            $invoiceQuery = $invoiceQuery->where('building_id', $request->building_id);
        }

        return new InvoiceStatsResource($invoiceQuery);
    }

    public function edit(InvoiceUpdateRequest $request, Invoice $invoice)
    {
        $documentUrl = optimizeDocumentAndUpload($request->file);

        $audit = InvoiceAudit::create([
            'invoice_id' => $invoice->id,
            'building_id' => $invoice->building_id,
            'contract_id' => $invoice->contract_id,
            'invoice_number' => $invoice->invoice_number,
            'wda_id' => $invoice->wda_id,
            'date' => $invoice->date,
            'document' => $invoice->document,
            'created_by' => $invoice->created_by,
            'status' => $invoice->status,
            'remarks' => $invoice->remarks,
            'status_updated_by' => $invoice->status_updated_by,
            'vendor_id' => $invoice->vendor_id,
            'invoice_amount' => $invoice->invoice_amount,

        ]);

        $request->merge([
            'document' => $documentUrl,
            'status' => 'pending',
        ]);

        $invoice->update($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Invoice resubmited successfully!',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function listInvoice(Request $request, Vendor $vendor)
    {

        // Get the date filter or use the current month and year
        $dateFilter = $request->input('date', Carbon::now()->format('F Y'));

        // Parse the date filter to get the start and end of the month
        $startDate = Carbon::createFromFormat('F Y', $dateFilter)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $dateFilter)->endOfMonth();

        $invoicesQuery = Invoice::where('vendor_id', $vendor->id)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->has('building_id') && !empty($request->building_id)) {
            $invoicesQuery = $invoicesQuery->where('building_id', $request->building_id);
        }
        if ($request->has('status') && !empty($request->status) && $request->status != 'all') {
            $invoicesQuery = $invoicesQuery->where('status', $request->status);
        }

        return InvoiceResource::collection($invoicesQuery->latest()->paginate(10));
    }

    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }
}
