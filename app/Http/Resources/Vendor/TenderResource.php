<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\Services\ServiceResource;
use App\Models\Master\Service;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();

        $tenderData = DB::table('tender_vendors')->where([
            'tender_id' => $this->id,
            'vendor_id' => $vendor->id
        ])->first();

        return [
            'id' => $this->id,
            'buildign' => $this->building->name,
            'end_date' => $this->end_date,
            'document' => Storage::disk('s3')->url($this->document),
            'contract_type' => "AMC",
            'status' => $tenderData?->status,
            'services' =>  $this->service_id ? Service::find($this->service_id)->name : 'NA'
        ];
    }
}
