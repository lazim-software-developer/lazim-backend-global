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
        $date = $request->input('date');
        $buildingId = $request->input('building_id');

        
        $generals = GeneralFund::where('type','General Fund')->where('credited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        $expenses = GeneralFund::where('type','General Fund')->where('debited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        

        return view('partials.general-fund-statement', ['generals' => $generals, 'expenses' => $expenses]);
    }

    public function getGeneralFundMollak(Request $request){

        $date = $request->input('date');
        $buildingId = $request->input('building_id');

        $virtualReceipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Virtual Account Transfer'])->where('receipt_date', $date)->sum('receipt_amount');
        $otherReceipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Noqodi Payment'])->where('receipt_date', $date)->get()
                                                                ->sum(function ($receipt) {
                                                                    $noqodiInfo = json_decode($receipt->noqodi_info, true);
                                                                    return $noqodiInfo['generalFundAmount'] ?? 0;
                                                                });
        $receipts = $virtualReceipts+$otherReceipts;

        $expenses = Invoice::where(['building_id' => $buildingId,'status' => 'approved'])->whereNotNull('payment')->where('date', $date)->get();

        $totalExpense = number_format($expenses?->sum('payment'),2);

        return view('partials.general-fund-statement-mollak', ['receipt' => $receipts, 'expenses' => $expenses, 'totalExpense' => $totalExpense]);
    }
}
