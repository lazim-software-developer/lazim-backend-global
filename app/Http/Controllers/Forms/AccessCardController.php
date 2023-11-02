<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateAccessCardFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Forms\AccessCard;

class AccessCardController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateAccessCardFormsRequest $request)
    {
        // Handle multiple images
        $document_paths = [
            'passport',
            'tenancy',
            'vehicle_registration',
        ];
        
        $data = $request->all();
        foreach ($document_paths as $document) {
            $file = $request->file($document);
            $data[$document] = optimizeDocumentAndUpload($file, 'dev');
        }
        $data['user_id'] = auth()->user()->id;
        $data['mobile']= auth()->user()->phone;
        $data['email'] = auth()->user()->email;
        AccessCard::create($data);
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Access card submitted successfully!',
            'errorCode' => 201,
        ]))->response()->setStatusCode(201);
    }
}
