<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\EscalationMatrixRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Vendor\VendorEscalationMatrix;
use Illuminate\Http\Request;

class EscalationMatrixController extends Controller
{
    public function store(EscalationMatrixRequest $request)
    {   
        if(!(VendorEscalationMatrix::where('vendor_id', $request->vendor_id)->where('escalation_level', $request->escalation_level))->first()){
        $escalation = VendorEscalationMatrix::create($request->all());
            return (new CustomResponseResource([
                'title' => 'Escalation Matrix added!',
                'message' => "",
                'errorCode' => 201,
                'status' => 'success',
            ]))->response()->setStatusCode(201);
        }
        return (new CustomResponseResource([
            'title' => 'Escalation Level exists!',
            'message' => " Escalation Level already exists, please enter a different level",
            'errorCode' => 400,
            'status' => 'error',
        ]))->response()->setStatusCode(400);
    }
}
