<?php

namespace App\Http\Controllers;

use App\Models\OwnerAssociationInvoice as ModelsOwnerAssociationInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Support\View\Components\Modal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use NumberFormatter;

class OwnerAssociationInvoice extends Controller
{
    public function invoice(Request $request)
    {
        $id = session('invoice_data');
        if($request->data){
            $id=$request->data;
        }
        $data = ModelsOwnerAssociationInvoice::findOrFail($id);
        $total = ($data['quantity'] * $data['rate']) + (($data['quantity'] * $data['rate'] * $data['tax'])/100);
        $data['total'] = $total;
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $totalWords = ucwords($formatter->format($total));
        $data['totalWords'] = $totalWords;
        // redirected to invoice blade file
        $pdf = Pdf::loadView('owner-association-invoice', compact('data'));
        return $pdf->download('invoice.pdf');
    //    return view('owner-association-invoice',['data' => $data]);
    }
}
