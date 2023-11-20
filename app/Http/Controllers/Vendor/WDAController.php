<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CreateWDARequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\WDAResource;
use App\Models\Accounting\WDA;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WDAController extends Controller
{
    public function index(Request $request, Vendor $vendor)
    {
        // Get the date filter or use the current month and year
        $dateFilter = $request->input('date', Carbon::now()->format('F Y'));

        // Parse the date filter to get the start and end of the month
        $startDate = Carbon::createFromFormat('F Y', $dateFilter)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $dateFilter)->endOfMonth();

        $wdaQuery = WDA::where('vendor_id', $vendor->id)
        ->whereBetween('date', [$startDate, $endDate]);

        if ($request->has('building_id') && !empty($request->building_id)) {
            $wdaQuery->where('building_id', $request->building_id);
        }
        if($request->has('status') && !empty($request->status)) {
            $wdaQuery->where('status', $request->status);
        }
        return WDAResource::collection($wdaQuery->latest()->paginate(10));
    }

    public function store(CreateWDARequest $request)
    {   
        $document = optimizeDocumentAndUpload($request->document);

        $name = auth()->user()->technicianVendors()->first()->vendor->name;
        $wda_number = strtoupper(substr($name, 0, 2)).date('YmdHis');
    
        $request->merge([
            'document' => $document,
            'created_by' => auth()->user()->id,
            'status' => 'pending',
            'vendor_id' => auth()->user()->technicianVendors()->first()->vendor_id,
            'wda_number' => $wda_number
        ]);

        WDA::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'WDA created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }

    public function show(WDA $wda)
    {
        return new WDAResource($wda);
    }
}
