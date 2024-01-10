<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OwnerAssociationInvoice extends Controller
{
    public function invoice(Request $request)
    {
        $data = session('invoice_data');
        $oam = session('oam');
        $building = session('building');
        $total = session('total');
        $totalWords = session('totalWords');
        // dd($oam->name);
       return view('owner-association-invoice',['data' => $data,'oam' => $oam,'building' => $building ,'total' => $total,'totalWords'=> $totalWords]);
    }
}
