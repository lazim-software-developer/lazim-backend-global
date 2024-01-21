<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\GeneralFund;
use Illuminate\Http\Request;

class ReserveFundController extends Controller
{
    public function getReserveFund(Request $request)
    {
        $date = $request->input('date');
        $buildingId = $request->input('building_id');
                                                                
        $reserves = GeneralFund::where('type','Reserve Fund')->where('credited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        $expenses = GeneralFund::where('type','Reserve Fund')->where('debited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        

        return view('partials.reserve-fund-statement', ['reserves' => $reserves, 'expenses' => $expenses]);
    }
}
