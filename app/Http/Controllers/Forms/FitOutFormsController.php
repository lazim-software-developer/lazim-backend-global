<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFitOutFormsRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Forms\FitOutForm;

class FitOutFormsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateFitOutFormsRequest $request)
    {
        FitOutForm::create([
            'building_id' => $request->building_id,
            'flat_id' => $request->flat_id,
            'contractor_name' => $request->name,
            'phone'=> auth()->user()->phone,
            'email' =>auth()->user()->email,
            'user_id'=> auth()->user()->id,
            'undertaking_of_waterproofing'=>$request->undertaking_of_waterproofing,
            'no_objection'=>$request->no_objection

        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Fit-out created successfully!',
            'errorCode' => 201,
        ]))->response()->setStatusCode(201);
    }
}
