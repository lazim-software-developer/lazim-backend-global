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
            foreach($oamReceipts as $k => $receipt){
                if($receipt->flat->users->count()){
                    $data = [];
                    $data["voucherDate"] = $receipt->receipt_date;
                    $data["voucherNumber"] = $receipt->receipt_number;
                    $data["narration"] = "Transaction Reference: " . $receipt->transaction_reference;
                    $data["voucherType"] = "Sales";

                    $data["voucherDetail"] = [];
                    $data["voucherDetail"]["debit"] = [
                        "ledgerName" => $receipt->flat->property_number . "-" . $receipt->flat->users->first()->first_name . $receipt->flat->users[0]->last_name . " Ledger",
                        "transactionType" => "Debit",
                        "amount" => $receipt->receipt_amount
                    ];

                    $data["voucherDetail"]["credit"] = [
                        "ledgerName" => "General Fund & Reserve Fund 2024",
                        "transactionType" => "Credit",
                        "amount" => $receipt->receipt_amount
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
            ], 200);
        }
    }
}
