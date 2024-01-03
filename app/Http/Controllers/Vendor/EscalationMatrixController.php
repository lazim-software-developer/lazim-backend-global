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
    public function store(EscalationMatrixRequest $request, Vendor $vendor)
    {
        if (VendorEscalationMatrix::where('vendor_id', $vendor->id)->where('active', 1)->where('escalation_level', $request->escalation_level)->exists()) {
            return (new CustomResponseResource([
                'title' => 'Escalation Level exists!',
                'message' => " Escalation Level already exists, please enter a different level",
                'code' => 400,
                'status' => 'error',
            ]))->response()->setStatusCode(400);
        }

        $request->merge(['vendor_id' => $vendor->id]);

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
        $escalation = VendorEscalationMatrix::where(['vendor_id' => $vendor->id, 'active' => 1])->get();
        return VendorEscalationMatrixResource::collection($escalation);
    }

    // Delete escalation matrix
    public function delete(VendorEscalationMatrix $escalationmatrix) {
        if($escalationmatrix->active == 0) {
            return (new CustomResponseResource([
                'title' => 'Escalation Matrix already deleted',
                'message' => "",
                'code' => 200,
                'status' => 'success',
            ]))->response()->setStatusCode(200);
        }

        $escalationmatrix->update(['active' => 0]);

        return (new CustomResponseResource([
            'title' => 'Escalation Matrix deleted!',
            'message' => "",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }
    public function exists(Vendor $vendor)
    {
        $escalation = VendorEscalationMatrix::where(['vendor_id' => $vendor->id, 'active' => 1])->exists();
        return [
            'escalation_exist' => $escalation ? true : false,
        ];
    }
}
