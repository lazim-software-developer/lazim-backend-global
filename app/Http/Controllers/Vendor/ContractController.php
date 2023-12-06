<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\ContractResource;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request,Vendor $vendor)
    {
        $year = now()->year;
        if ($request->has('year')){
            $year = $request->year;
        }
        $Contracts = Contract::where("vendor_id", $vendor->id)
        ->where(function ($query) use ($year) {
            $query->whereYear('start_date', $year)
                ->orWhereYear('end_date', $year);
        })->get();
        if ($request->has('building_id') && !empty($request->building_id)) {
            $Contracts = $Contracts->where('building_id', $request->building_id);
        }

        return ContractResource::collection($Contracts);
    }
}
