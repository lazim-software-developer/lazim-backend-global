<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
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
}
