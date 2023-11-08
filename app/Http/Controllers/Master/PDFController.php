<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Building\Flat;
use App\Models\Forms\SaleNOC;
use App\Models\OwnerAssociation;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function serviceChargePDF(SaleNOC $saleNOC) {
        $flat = Flat::where('id', $saleNOC->flat_id)->first();
        $ownerAssociation = OwnerAssociation::where('id', $flat->owner_association_id)->value('name');

        $data = [
            'username' => $saleNOC->signing_authority_name,
            'email' => $saleNOC->signing_authority_email,
            'phone' => $saleNOC->signing_authority_phone,
            'flat' => $flat->property_number,
            'building' => $flat->building->value('name'),
            'ownerAssociation' => $ownerAssociation ?? 'NA'
        ];

        $pdf = Pdf::loadView('pdf.service-charge', compact('data'));
        return $pdf->download('service-charge.pdf');
    }
}
