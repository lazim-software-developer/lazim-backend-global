<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\ResidentialFormRequest;
use App\Models\Building\Building;
use App\Models\ResidentialForm;

class ResidentialFormController extends Controller
{
    public function store(ResidentialFormRequest $request)
    {
        $validated = $request->validated();
        
        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $validated['passport_url'] = optimizeDocumentAndUpload($request->file('passport_url'));
        $validated['emirates_url'] = optimizeDocumentAndUpload($request->file('emirates_url'));
        $validated['title_deed_url'] = optimizeDocumentAndUpload($request->file('title_deed_url'));

        $validated['user_id'] = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;

        $residentialForm = ResidentialForm::create($validated);

        return response()->json([
            'message' => 'Form successfully created',
            'data' => $residentialForm
        ], 201);
    }
}
