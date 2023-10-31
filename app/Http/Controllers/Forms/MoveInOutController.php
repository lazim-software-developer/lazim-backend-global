<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Document;
use App\Models\Forms\Form;
use App\Models\Forms\MoveInOut;
use App\Models\User\User;

class MoveInOutController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFormRequest $request)
    {
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
        
        MoveInOut::create($data);
        
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Form submitted successfully!',
            'errorCode' => 201,
            'status' => 'success',
        ]))->response()->setStatusCode(201);
    }
}
