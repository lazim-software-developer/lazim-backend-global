<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\ContractResource;
use App\Http\Resources\Vendor\TenderResource;
use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenderController extends Controller
{
    public function index() {
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        $tendersIds = DB::table('tender_vendors')
        ->where('vendor_id', $vendor->id)
        ->latest()
        ->pluck('tender_id');
        
        $tenders = Tender::whereIn('id', $tendersIds)->paginate(10);
        
        return TenderResource::collection($tenders);
    }

    // Create proposal
    public function store(Tender $tender, Request $request) {

        $filePath = optimizeDocumentAndUpload($request->file('file'), 'dev');

        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        Proposal::create([
            'tender_id' => $tender->id,
            'amount' => $request->amount,
            'submitted_by' => auth()->user()->id,
            'submitted_on' => now(),
            'document' => $filePath
        ]);

        DB::table('tender_vendors')->where([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id
        ])->update([
            'status' => 'approved'
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Proposal created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }
}
