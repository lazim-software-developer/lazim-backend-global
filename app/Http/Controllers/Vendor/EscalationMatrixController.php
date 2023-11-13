<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\EscalationMatrixRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorEscalationMatrixResource;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorEscalationMatrix;

class EscalationMatrixController extends Controller
{
    public function store(EscalationMatrixRequest $request)
    {
        if (VendorEscalationMatrix::where('vendor_id', $request->vendor_id)->where('escalation_level', $request->escalation_level)->exists()) {
            return (new CustomResponseResource([
                'title' => 'Escalation Level exists!',
                'message' => " Escalation Level already exists, please enter a different level",
                'code' => 400,
                'status' => 'error',
            ]))->response()->setStatusCode(400);
        }

        // If donesnot exists, create new
        VendorEscalationMatrix::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Escalation Matrix added!',
            'message' => "",
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }

    public function show(Vendor $vendor)
    {
        $escalation = VendorEscalationMatrix::where('vendor_id', $vendor->id)->get();
        return VendorEscalationMatrixResource::collection($escalation);
    }
}
