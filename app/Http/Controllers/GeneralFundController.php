<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMReceipts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralFundController extends Controller
{
    public function getGeneralFund(Request $request)
    {
        $year = $request->input('year');
        $buildingId = $request->input('building_id');

        $virtualReceipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Virtual Account Transfer'])->whereYear('receipt_date', $year)->sum('receipt_amount');
        $otherReceipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Noqodi Payment'])->whereYear('receipt_date', $year)->get()
                                                                ->sum(function ($receipt) {
                                                                    $noqodiInfo = json_decode($receipt->noqodi_info, true);
                                                                    return $noqodiInfo['generalFundAmount'] ?? 0;
                                                                });
        $receipts = $virtualReceipts+$otherReceipts;

        $expenses = Invoice::where(['building_id' => $buildingId,'status' => 'approved'])->whereNotNull('payment')->whereYear('date', $year)->get();

        $totalExpense = number_format($expenses?->sum('payment'),2);

        return view('partials.general-fund-statement', ['receipt' => $receipts, 'expenses' => $expenses, 'totalExpense' => $totalExpense]);
    }
}
