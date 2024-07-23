<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\ResidentialFormRequest;
use App\Jobs\Forms\ResidentialFormRequestJob;
use App\Models\AccountCredentials;
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

        if ($request->hasFile('file_title_deed_url')) {
            $validated['title_deed_url'] = optimizeDocumentAndUpload($request->file('file_title_deed_url'));
        }
        if ($request->hasFile('file_tenancy_contract')) {
            $validated['tenancy_contract'] = optimizeDocumentAndUpload($request->file('file_tenancy_contract'));
        }

        $validated['user_id']              = auth()->user()->id;
        $validated['owner_association_id'] = $ownerAssociationId;
        $validated['ticket_number']        = generate_ticket_number("FV");

        $residentialForm  = ResidentialForm::create($validated);
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id ?? $ownerAssociationId;
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        ResidentialFormRequestJob::dispatch(auth()->user(), $residentialForm, $mailCredentials);

        return response()->json([
            'message' => 'Form successfully created',
            'data'    => $residentialForm,
        ], 201);
    }
}
