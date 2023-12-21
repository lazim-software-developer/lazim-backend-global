<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Document;
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
        $document->update([
            'url' => optimizeDocumentAndUpload($request->tl_document),
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'TL Details updated!',
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);

    }
}
