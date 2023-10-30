<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Document;
use App\Models\Forms\Form;
use App\Models\User\User;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateFormRequest $request)
    {
        Form::create($request->all());

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filePath = optimizeDocumentAndUpload($image, 'dev');
                $currentDate = date('Y-m-d');

                //TODO: NEED TO CHANGE EXPIRY_DATE LOGIC
                $request->merge([
                    'documentable_id' => auth()->user()->id,
                    'status' => 'submitted',
                    'url' => $filePath,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($currentDate))),
                    'documentable_type' => User::class,
                    'name' => $request->document_name,
                ]);

                Document::create($request->all());
            }
            return (new CustomResponseResource([
                'title' => 'Success',
                'message' => 'Move-IN created successfully!',
                'errorCode' => 201,
            ]))->response()->setStatusCode(201);
        }
    }
}
