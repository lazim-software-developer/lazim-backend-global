<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\ContractResource;
use App\Http\Resources\Vendor\WDAContractResource;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
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
        if ($request->has('contract_type') && !empty($request->contract_type)) {
            $Contracts = $Contracts->where('contract_type', $request->contract_type);
        }

        return ContractResource::collection($Contracts);
    }

    public function listContracts(Vendor $vendor)
    {
        $contracts = Contract::where("vendor_id", $vendor->id)->where('end_date','>=', Carbon::now()->toDateString())->get();

        return WDAContractResource::collection($contracts);
    }
}
