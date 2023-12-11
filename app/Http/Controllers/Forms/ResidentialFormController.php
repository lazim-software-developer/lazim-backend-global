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

        $validated['passport_url'] = optimizeDocumentAndUpload($request->file('file_passport_url'));
        $validated['emirates_url'] = optimizeDocumentAndUpload($request->file('file_emirates_url'));
        $validated['title_deed_url'] = optimizeDocumentAndUpload($request->file('file_title_deed_url'));
        $validated['tenancy_contract'] = optimizeDocumentAndUpload($request->file('file_tenancy_contract'));

        $validated['user_id'] = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;

        $residentialForm = ResidentialForm::create($validated);

        return response()->json([
            'message' => 'Form successfully created',
            'data' => $residentialForm
        ], 201);
    }
}
