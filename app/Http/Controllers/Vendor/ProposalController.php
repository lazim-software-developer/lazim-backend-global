<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\ProposalResource;
use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index(Request $request,Vendor $vendor){
        $year = now()->year;
        if ($request->has('year')){
            $year = $request->year;
        }
        $proposal = Proposal::where("vendor_id", $vendor->id)
        ->where(function ($query) use ($year) {
            $query->whereYear('submitted_on', $year);
        })->get();
        if ($request->has('building_id') && !empty($request->building_id)) {
            $tendors = Tender::where('building_id',$request->building_id)->pluck('id');
            $proposal = $proposal->whereIn('tender_id', $tendors);
        }

        return ProposalResource::collection($proposal);
    }
}
