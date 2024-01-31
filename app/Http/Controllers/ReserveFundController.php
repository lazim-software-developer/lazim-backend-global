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

    public function getReserveFundMollak(Request $request)
    {
        $date = $request->input('date');
        $buildingId = $request->input('building_id');

        $receipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Noqodi Payment'])->whereDate('receipt_date', $date)->get()
                                                                ->sum(function ($receipt) {
                                                                    $noqodiInfo = json_decode($receipt->noqodi_info, true);
                                                                    return $noqodiInfo['reservedFundAmount'] ?? 0;
                                                                });

        // $expenses = Invoice::where(['building_id' => $buildingId,'status' => 'approved'])->whereNotNull('payment')->whereYear('date', $year)->get();

        // $totalExpense = number_format($expenses?->sum('payment'),2);

        return view('partials.reserve-fund-statement-mollak', ['receipt' => $receipts]);
    }
}
