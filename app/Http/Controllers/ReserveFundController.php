<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMReceipts;
use Illuminate\Http\Request;

class ReserveFundController extends Controller
{
    public function getReserveFund(Request $request)
    {
        $year = $request->input('year');
        $buildingId = $request->input('building_id');

        $receipts= OAMReceipts::where(['building_id'=>$buildingId,'payment_mode'=>'Noqodi Payment'])->whereYear('receipt_date', $year)->get()
                                                                ->sum(function ($receipt) {
                                                                    $noqodiInfo = json_decode($receipt->noqodi_info, true);
                                                                    return $noqodiInfo['reservedFundAmount'] ?? 0;
                                                                });

        // $expenses = Invoice::where(['building_id' => $buildingId,'status' => 'approved'])->whereNotNull('payment')->whereYear('date', $year)->get();

        // $totalExpense = number_format($expenses?->sum('payment'),2);

        return view('partials.reserve-fund-statement', ['receipt' => $receipts]);
    }
}
