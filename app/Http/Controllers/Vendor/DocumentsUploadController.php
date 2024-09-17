<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\DocumentsUploadRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorDocumentResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class DocumentsUploadController extends Controller
{
    public function documentsUpload(DocumentsUploadRequest $request, Vendor $vendor)
    {
        $status = 0;
        foreach($request->docs as $key => $value) {
            $path = optimizeDocumentAndUpload($value);
            $request->merge([
                'name' => $key,
                'documentable_id' => $vendor->id,
                'status'    => 'pending',
                'documentable_type'   => Vendor::class,
                'document_library_id' => DocumentLibrary::where('label', $key)->value('id'),
                'url' => $path,
                'owner_association_id' => $vendor->owner_association_id,
            ]);
            $docs = Document::where('documentable_id',$vendor->id)->where('document_library_id',DocumentLibrary::where('label', $key)->value('id'))
                    ->where('owner_association_id',$vendor->owner_association_id)->where('name',$key);
            if(!$docs->exists()) {
                  Document::create($request->all());
            }
            else{
                $docs->update(['url' => $path]);
                $status = 1;
            }
        }
        if ($status == 1){
            $vendor->update(['status' => null, 'remarks' => null]);
        }

        return (new CustomResponseResource([
            'title' => 'Documents upload successful!',
            'message' => "Documents uploaded successfully!",
            'code' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }

    public function showDocuments(Vendor $vendor)
    {
        $documents = Document::where('documentable_id', $vendor->id)->get();

        return VendorDocumentResource::collection($documents);
    }

    public function listDocuments(Vendor $vendor)
    {
        $documents = Document::where('documentable_id', $vendor->id)->get();
        $data = $documents->mapWithKeys(function ($document) {
            return [$document->documentLibrary->name => env('AWS_URL') . '/' . $document->url];
        });
        return $data;
    }

    public function showRiskPolicy(Vendor $vendor)
    {
        $document = Document::where('documentable_id',$vendor->id)->where('name','risk_policy')->latest()->first();
        return [
            'risk_policy_expiry' => $document?->expiry_date,
            'risk_policy_document' => env('AWS_URL').'/'.$document?->url,
        ];
    }

    public function updateRiskPolicy(Request $request,Vendor $vendor){

        $document = Document::where('documentable_id',$vendor->id)->where('name','risk_policy')->latest()->first();
        if(empty($document)){
            $document = Document::create([
                "name" => "risk_policy",
                "document_library_id" => DocumentLibrary::where('name','Risk policy')->where('type', 'vendor')->first()->id,
                "owner_association_id" => $vendor->owner_association_id,
                "status" => 'pending',
                "documentable_id" => $vendor->id,
                "expiry_date" => $request->risk_policy_expiry,
                "documentable_type" => Vendor::class,
            ]);
        }

        $document->update([
            'url' => optimizeDocumentAndUpload($request->file('risk_policy_document')),
            'expiry_date' => $request->risk_policy_expiry,
            'status' => 'pending'
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Risk Policy Details updated!',
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);

    }
}
