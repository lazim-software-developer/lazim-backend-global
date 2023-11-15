<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Forms\MoveInOut;

class MoveInOutController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFormRequest $request)
    {
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        // Handle multiple images
        $document_paths = [
            'handover_acceptance',
            'receipt_charges',
            'contract',
            'title_deed',
            'passport',
            'dewa',
            'cooling_registration',
            'gas_registration',
            'vehicle_registration',
            'movers_license',
            'movers_liability',
        ];

        $data = $request->all();  // Get all request data

        foreach ($document_paths as $document) {
            if ($request->hasFile($document)) {
                $file = $request->file($document);
                $data[$document] = optimizeDocumentAndUpload($file, 'dev');
            }
        }

        $data['name'] = auth()->user()->first_name;
        $data['phone']= auth()->user()->phone;
        $data['email']= auth()->user()->email;
        $data['user_id']= auth()->user()->id;
        $data['owner_association_id']= $ownerAssociationId;
        
        MoveInOut::create($data);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Form submitted successfully!',
            'code' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }
}
