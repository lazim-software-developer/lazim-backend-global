<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\ResidentialFormRequest;
use App\Jobs\Forms\ResidentialFormRequestJob;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use Filament\Facades\Filament;

class ResidentialFormController extends Controller
{
    public function store(ResidentialFormRequest $request)
    {
        $validated = $request->validated();

        $ownerAssociationId = Building::find($request->building_id)->owner_association_id;

        $validated['passport_url'] = optimizeDocumentAndUpload($request->file('file_passport_url'));
        $validated['emirates_url'] = optimizeDocumentAndUpload($request->file('file_emirates_url'));

        if ($request->hasFile('file_title_deed_url')){
            $validated['title_deed_url'] = optimizeDocumentAndUpload($request->file('file_title_deed_url'));
        }
        if ($request->hasFile('file_tenancy_contract')){
            $validated['tenancy_contract'] = optimizeDocumentAndUpload($request->file('file_tenancy_contract'));
        }

        $validated['user_id'] = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;
        $validated['ticket_number'] = generate_ticket_number("FV");

        $residentialForm = ResidentialForm::create($validated);
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id ?? $ownerAssociationId;
        $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest() ->first()?->email ?? env('MAIL_FROM_ADDRESS');

        ResidentialFormRequestJob::dispatch(auth()->user(), $residentialForm,$emailCredentials);

        return response()->json([
            'message' => 'Form successfully created',
            'data' => $residentialForm
        ], 201);
    }
}
