<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\GeneralFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrialBalanceController extends Controller
{
    public function getTrialBalance(Request $request)
    {
        Log::info('here');
        $date = $request->input('date');
        $buildingId = $request->input('building_id');

        $generals = GeneralFund::where('type','General Fund')->where('credited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        $reserves = GeneralFund::where('type','Reserve Fund')->where('credited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        $expenses = GeneralFund::where('debited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get();
        $oamInvoices = OAMInvoice::where('building_id',$buildingId)->where('invoice_date', $date)->get();
        $assets = $oamInvoices->filter(function ($asset){
             $receipt = OAMReceipts::where('building_id', $asset->building_id)->where('flat_id' , $asset->flat_id)->where('receipt_period', $asset->invoice_period)->first();
             if(!$receipt){
                return $asset;
             }
        });
        $invoices = Invoice::where('building_id',$buildingId)->where('date', $date)->where('payment', null)->get();
        $generalSurplus = $generals->sum('credited_amount') - GeneralFund::where('type','General Fund')->where('debited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get()->sum('debited_amount');
        $reserveSurplus = $reserves->sum('credited_amount') - GeneralFund::where('type','Reserve Fund')->where('debited_amount','>',0)->where('building_id',$buildingId)->where('statement_date', $date)->get()->sum('debited_amount');
        return view('partials.trial-balance', ['generals' => $generals, 'reserves' => $reserves, 'expenses' => $expenses,'assets' => $assets, 'invoices' => $invoices, 'generalSurplus' => $generalSurplus, 'reserveSurplus' => $reserveSurplus]);
    }
}
