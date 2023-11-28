<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\ContractResource;
use App\Http\Resources\Vendor\TenderResource;
use App\Models\Accounting\Tender;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenderController extends Controller
{
    public function index() {
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        $tendersIds = DB::table('tender_vendors')
        ->where('vendor_id', $vendor->id)
        ->latest()
        ->pluck('tender_id');
        
        $tenders = Tender::whereIn('id', $tendersIds)->paginate(10);
        
        return TenderResource::collection($tenders);
    }
}
