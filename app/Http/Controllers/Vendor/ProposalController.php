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
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->start_date; // Assuming the start date is January 1st of the specified year
            $endDate = $request->end_date;   // Assuming the end date is December 31st of the specified year
        }
        
        $proposal = Proposal::where("vendor_id", $vendor->id)
            ->when(isset($startDate) && isset($endDate), function ($query) use ($startDate, $endDate) {
                $query->whereBetween('submitted_on', [$startDate, $endDate]);
            })
            ->get();
        if ($request->has('building_id') && !empty($request->building_id)) {
            $tendors = Tender::where('building_id',$request->building_id)->pluck('id');
            $proposal = $proposal->whereIn('tender_id', $tendors);
        }

        return ProposalResource::collection($proposal);
    }
}
