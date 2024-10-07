<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubContractorsRequest;
use App\Http\Resources\SubContractorsResource;
use App\Jobs\SendSubcontractorCreatedMailJob;
use App\Models\SubContractor;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class SubContractorsController extends Controller
{
    public function index(Vendor $vendor, Request $request)
    {
        $subContractors = $vendor->subContractors();
        return SubContractorsResource::collection($subContractors->paginate(10));
    }
    public function store(Vendor $vendor, SubContractorsRequest $request)
    {
        $subContract = $vendor->subContractors()->create($request->all());

        if ($request->has('additional_doc') && isset($request->additional_doc)) {
            $subContract->update(['additional_doc' => optimizeDocumentAndUpload($request->additional_doc)]);
        }

        $subContract->update([
            'trade_licence'    => optimizeDocumentAndUpload($request->trade_licence),
            'contract_paper'   => optimizeDocumentAndUpload($request->contract_paper),
            'agreement_letter' => optimizeDocumentAndUpload($request->agreement_letter),
        ]);
        $subContract->services()->sync($request->services);

        SendSubcontractorCreatedMailJob::dispatch($subContract);

        return SubContractorsResource::make($subContract);
    }
    public function edit(Vendor $vendor, SubContractor $subContract, SubContractorsRequest $request)
    {
        $subContract->update($request->all());

        if ($request->has('additional_doc') && isset($request->additional_doc)) {
            $subContract->update(['additional_doc' => optimizeDocumentAndUpload($request->additional_doc)]);
        }
        $subContract->update([
            'trade_licence'    => optimizeDocumentAndUpload($request->trade_licence),
            'contract_paper'   => optimizeDocumentAndUpload($request->contract_paper),
            'agreement_letter' => optimizeDocumentAndUpload($request->agreement_letter),
        ]);
        $subContract->services()->sync($request->services);

        return SubContractorsResource::make($subContract);
    }
    public function update(Vendor $vendor, SubContractor $subContract, Request $request)
    {
        if (isset($request->active) && $request->active) {
            $subContract->update(['active'=>1]);
        }else{
            $subContract->update(['active'=>0]);
        }

        return SubContractorsResource::make($subContract);
    }
}
