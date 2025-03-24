<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubContractorEditRequest;
use App\Http\Requests\SubContractorsRequest;
use App\Http\Resources\SubContractorsResource;
use App\Jobs\SendSubcontractorCreatedMailJob;
use App\Models\SubContractor;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubContractorsController extends Controller
{
    public function index(Vendor $vendor, Request $request)
    {
        $buildingIds = DB::table('building_vendor')->where('vendor_id',$vendor->id)->where('active',true)
            ->pluck('building_id');
        $subContractors = $vendor->subContractors()->whereIn('building_id',$buildingIds);
        return SubContractorsResource::collection($subContractors->paginate($request->paginate ?? 10));
    }
    public function store(Vendor $vendor, SubContractorsRequest $request)
    {
        if ($request->has('building_id') && isset($request->building_id)) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

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

        // Fetch the vendor name
        $vendor_name = $vendor->name;

        SendSubcontractorCreatedMailJob::dispatch($subContract, $vendor_name);

        return SubContractorsResource::make($subContract);
    }
    public function edit(Vendor $vendor, SubContractor $subContract, SubContractorEditRequest $request)
    {
        if ($request->has('building_id') && isset($request->building_id)) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        $updateData = $request->except(['additional_doc', 'trade_licence', 'contract_paper', 'agreement_letter']);
        $subContract->update($updateData);

        if ($request->has('additional_doc') && isset($request->additional_doc)) {
            $subContract->update(['additional_doc' => optimizeDocumentAndUpload($request->additional_doc)]);
        }
        if($request->has('trade_licence') && isset($request->trade_licence)){
            $subContract->update(['trade_licence' => optimizeDocumentAndUpload($request->trade_licence)]);
        }
        if($request->has('contract_paper') && isset($request->contract_paper)){
            $subContract->update(['contract_paper' => optimizeDocumentAndUpload($request->contract_paper)]);
        }
        if($request->has('agreement_letter') && isset($request->agreement_letter)){
            $subContract->update(['agreement_letter' => optimizeDocumentAndUpload($request->agreement_letter)]);
        }
        $subContract->services()->sync($request->services);

        return SubContractorsResource::make($subContract);
    }
    public function update(Vendor $vendor, SubContractor $subContract, Request $request)
    {
        if ($request->has('building_id') && isset($request->building_id)) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        if (isset($request->active) && $request->active) {
            $subContract->update(['active'=>1]);
        }else{
            $subContract->update(['active'=>0]);
        }

        return SubContractorsResource::make($subContract);
    }
}
