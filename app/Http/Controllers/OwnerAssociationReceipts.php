<?php

namespace App\Http\Controllers;

use App\Models\OwnerAssociationReceipt;
use Illuminate\Http\Request;
use NumberFormatter;

class OwnerAssociationReceipts extends Controller
{
    public function receipt(Request $request)
    {
        $id = session('receipt_data');
        if($request->data){
            $id=$request->data;
        }
        $data = OwnerAssociationReceipt::findOrFail($id);
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $totalWords = ucwords($formatter->format($data->amount));
        $data['totalWords'] = $totalWords;
        $data['received_in'] = ucwords($data->received_in);
        // redirected to invoice blade file
       return view('owner-association-receipts',['data'=>$data]);
    }
}
