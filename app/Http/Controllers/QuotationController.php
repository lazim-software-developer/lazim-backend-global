<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Jobs\QuotationMailJob;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function store(QuotationRequest $request)
    {
        $quotation = Quotation::create($request->all());

        QuotationMailJob::dispatch($quotation);

        return response()->json([
            'message' => 'Quotation submitted successfully!',
            'data' => $quotation,
        ], 201);
    }
}
