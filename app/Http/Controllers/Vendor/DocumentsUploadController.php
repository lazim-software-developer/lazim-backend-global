<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\DocumentsUploadRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class DocumentsUploadController extends Controller
{
    public function documentsUpload(DocumentsUploadRequest $request)
    {
        
        foreach($request->docs as $key => $value){
            $path = optimizeDocumentAndUpload($value);
            $request->merge([
                'status'    => 'pending',
                'documentable_type'   => Vendor::class,
                'document_library_id' => DocumentLibrary::where('name', '=', $key)->first()->id,
                'url' => $path,
                'owner_association_id' => Vendor::find($request->documentable_id)->owner_association_id,
            ]);
            $document = Document::create($request->all());

        }
        return (new CustomResponseResource([
            'title' => 'Document upload successfull!',
            'message' => "",
            'errorCode' => 201,
            'status' => 'success'
        ]))->response()->setStatusCode(201);
    }
}
