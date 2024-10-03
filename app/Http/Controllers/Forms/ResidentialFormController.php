<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\ResidentialFormRequest;
use App\Http\Resources\ResidentialFormResource;
use App\Jobs\Forms\ResidentialFormRequestJob;
use App\Models\AccountCredentials;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

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
    public function fmlist(Vendor $vendor)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
            ->whereIn('owner_association_id', $ownerAssociationIds)->pluck('building_id');

        $residentForms = ResidentialForm::whereIn('building_id', $buildingIds);

        return ResidentialFormResource::collection($residentForms->paginate(10));
    }
}
