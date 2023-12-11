<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Building\Flat;

class PaymentController extends Controller
{
    public function fetchServiceCharges(Flat $flat) {
        return $flat->oaminvoices()->paginate(10, ['invoice_pdf_link']);
    }
}
