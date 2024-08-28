<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class TLController extends Controller
{
    public function show(Vendor $vendor){
        return [
            'tl_number' => $vendor->tl_number,
            'tl_expiry' => $vendor->tl_expiry,
            'tl_document' => env('AWS_URL').'/'.Document::where('documentable_id',$vendor->id)->where('name','tl_document')->latest()->first()->url,
        ];
    }

    public function update(Request $request,Vendor $vendor){
        $vendor->update($request->all());
        $document = Document::where('documentable_id',$vendor->id)->where('name','tl_document')->latest()->first();
        if(empty($document)){
            $document = Document::create([
                "name" => "tl_document",
                "document_library_id" => DocumentLibrary::where('name','TL document')->where('type', 'vendor')->first()->id,
                "owner_association_id" => $vendor->owner_association_id,
                "status" => 'pending',
                "documentable_id" => $vendor->id,
                "expiry_date" => $request->tl_expiry,
                "documentable_type" => Vendor::class,
            ]);
        }

        $document->update([
            'url' => optimizeDocumentAndUpload($request->file('tl_document')),
            'expiry_date' => $request->tl_expiry,
            "status" => 'pending'
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'TL Details updated!',
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);

    }
}
