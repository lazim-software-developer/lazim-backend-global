<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplianceDocumentRequest;
use App\Http\Resources\ComplianceDocumentResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\ComplianceDocument;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceDocumentController extends Controller
{
    public function list(Request $request, Vendor $vendor)
    {
        $complianceDocument = $vendor->complianceDocuments()
            ->paginate($request->get('paginate', 10));

        return ComplianceDocumentResource::collection($complianceDocument);
    }

    public function store(Vendor $vendor,ComplianceDocumentRequest $request)
    {
        if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        $data = $request->all();

        $data['vendor_id'] = $vendor->id;
        $data['url'] = optimizeDocumentAndUpload($request->url);

        $complianceDocument = ComplianceDocument::create($data);

        return ComplianceDocumentResource::make($complianceDocument);

    }
    public function update(Vendor $vendor,ComplianceDocument $complianceDocument, ComplianceDocumentRequest $request)
    {
       if ($request->has('building_id')) {
            $oa_id = DB::table('building_owner_association')->where('building_id', $request->building_id)->where('active', true)->first()->owner_association_id;
        }

        $data = $request->all();

        if($request->has('url')){
            $data['url'] = optimizeDocumentAndUpload($request->url);
        }

        $complianceDocument->update($data);

        return ComplianceDocumentResource::make($complianceDocument);

    }
    public function dashboardList(Request $request, Vendor $vendor)
    {
        $complianceDocument = $vendor->complianceDocuments()
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->paginate($request->get('paginate', 10));

        return ComplianceDocumentResource::collection($complianceDocument);
    }
}
