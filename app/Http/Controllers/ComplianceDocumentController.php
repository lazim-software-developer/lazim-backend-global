<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplianceDocumentRequest;
use App\Http\Resources\ComplianceDocumentResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\ComplianceDocument;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class ComplianceDocumentController extends Controller
{
    public function list(Vendor $vendor)
    {
        $complianceDocument = $vendor->ComplianceDocuments;

        return ComplianceDocumentResource::collection($complianceDocument);
    }

    public function store(Vendor $vendor,ComplianceDocumentRequest $request)
    {
        $data = $request->all();

        $data['vendor_id'] = $vendor->id;
        $data['url'] = optimizeDocumentAndUpload($request->url);

        $complianceDocument = ComplianceDocument::create($data);

        return ComplianceDocumentResource::make($complianceDocument);

    }
    public function update(Vendor $vendor,ComplianceDocument $complianceDocument, ComplianceDocumentRequest $request)
    {
        $data = $request->all();

        if($request->has('url')){
            $data['url'] = optimizeDocumentAndUpload($request->url);
        }

        $complianceDocument->update($data);

        return ComplianceDocumentResource::make($complianceDocument);

    }
    public function dashboardList(Vendor $vendor)
    {
        $complianceDocument = $vendor->ComplianceDocuments->whereBetween('expiry_date', [now(), now()->addDays(30)]);

        return ComplianceDocumentResource::collection($complianceDocument);
    }
}
