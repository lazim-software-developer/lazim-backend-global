<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function serviceChargePDF(Flat $flat) {
        $ownerAssociation = OwnerAssociation::where('id', $flat->owner_association_id)->value('name');

        $data = [
            'username' => auth()->user()->first_name,
            'email' => auth()->user()->email,
            'phone' => auth()->user()->phone,
            'flat' => $flat->property_number,
            'building' => $flat->building->value('name'),
            'ownerAssociation' => $ownerAssociation ?? 'NA'
        ];

        $pdf = Pdf::loadView('pdf.service-charge', compact('data'));
        return $pdf->download('service-charge.pdf');
    }
}
