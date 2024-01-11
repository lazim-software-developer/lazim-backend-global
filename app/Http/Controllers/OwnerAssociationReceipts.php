<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OwnerAssociationReceipts extends Controller
{
    public function receipt()
    {
        $data = session('receipt_data');
        $oam = session('oam');
        $building = session('building');
        $words = session('words');
        // dd($oam->name);
       return view('owner-association-receipts',['data'=>$data, 'building'=>$building,'oam'=>$oam, 'words'=> $words]);
    }
}
