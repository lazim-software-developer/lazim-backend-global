<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\DocumentsUploadRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\VendorDocumentResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Vendor\Vendor;

class DocumentsUploadController extends Controller
{
    public function documentsUpload(DocumentsUploadRequest $request, Vendor $vendor)
    {
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

            Document::create($request->all());
        }

        return (new CustomResponseResource([
            'title' => 'Documents upload successful!',
            'message' => "",
            'code' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }

    public function showDocuments(Vendor $vendor)
    {
        $documents = Document::where('documentable_id', $vendor->id)->get();

        return VendorDocumentResource::collection($documents);
    }
}
