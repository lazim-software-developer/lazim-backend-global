<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\GeneralFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralFundController extends Controller
{
    public function getGeneralFund(Request $request)
    {
        $year = $request->input('year');
        $buildingId = $request->input('building_id');

        
        $generals = GeneralFund::where('type','General Fund')->where('credited_amount','>',0)->where('building_id',$buildingId)->whereYear('statement_date', $year)->paginate();
        $expenses = GeneralFund::where('type','General Fund')->where('debited_amount','>',0)->where('building_id',$buildingId)->whereYear('statement_date', $year)->paginate();
        

        return view('partials.general-fund-statement', ['generals' => $generals, 'expenses' => $expenses]);
    }
}
