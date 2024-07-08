<?php

namespace App\Http\Controllers\Api\Tally;

use App\Http\Controllers\Controller;
use App\Models\Accounting\OAMReceipts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TallyIntigrationController extends Controller
{
    public function getSalesVouhers(Request $request)
    {
        try {
            $request->validate([
                "fromDate" => "required",
                "toDate" => "required",
            ]);
            $oamReceipts = OAMReceipts::whereBetween('receipt_date', [$request->fromDate, $request->toDate])->get();
            $responseData = [];
            foreach($oamReceipts as $k => $value){
                if($value->flat->users->count()){
                    $data[] = [
                        "ledgerName" => "General Fund & Reserve Fund Ledger (Income)",
                        "transactionType" => "Credit",
                        "amount" => $value->receipt_amount
                    ];
                    $data[] = [
                        "ledgerName" => $value->flat->property_number . "-" . $value->flat->users->first()->first_name . $value->flat->users[0]->last_name . " Ledger",
                        "transactionType" => "Debit",
                        "amount" => $value->receipt_amount
                    ];

                    $responseData[] = $data;
                }

            }
            return response()->json([
                "result"=>"success",
                "total_vouchers"=> count($responseData),
                "vouchers"=> $responseData
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'result' => 'error',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
