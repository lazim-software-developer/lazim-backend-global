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
    public function index()
    {
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        $tendersIds = DB::table('tender_vendors')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->pluck('tender_id');

        $tenders = Tender::whereIn('id', $tendersIds)->paginate(10);

        return TenderResource::collection($tenders);
    }

    // Create proposal
    public function store(Tender $tender, Request $request)
    {
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        $proposalExists = Proposal::where(['tender_id' => $tender->id, 'vendor_id' => $vendor->id])->exists();

        if ($proposalExists) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'Ypu have already submitted proposal for this tender',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }
        $filePath = optimizeDocumentAndUpload($request->file('file'), 'dev');


        Proposal::create([
            'tender_id' => $tender->id,
            'amount' => $request->amount,
            'submitted_by' => $vendor->id,
            'submitted_on' => now(),
            'document' => $filePath,
            'vendor_id' => $vendor->id
        ]);

        DB::table('tender_vendors')->where([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id
        ])->update([
            'status' => 'applied'
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Proposal created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }
}
