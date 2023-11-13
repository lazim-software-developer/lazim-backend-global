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
        foreach($request->docs as $key => $value){
            $path = optimizeDocumentAndUpload($value);
            $request->merge([
                'status'    => 'pending',
                'documentable_type'   => Vendor::class,
                'document_library_id' => DocumentLibrary::where('name', $key)->first()->id,
                'url' => $path,
                'owner_association_id' => $vendor->owner_association_id,
            ]);
            $document = Document::create($request->all());
        }

        return (new CustomResponseResource([
            'title' => 'Document upload successfull!',
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
