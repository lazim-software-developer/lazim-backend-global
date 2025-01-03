<?php

namespace App\Http\Resources\User;

use App\Models\Bill;
use App\Models\OwnerAssociation;
use App\Models\RentalDetail;
use App\Models\UserApproval;
use Illuminate\Http\Request;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFlatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flat = FlatTenant::where(['flat_id' => $this->id, 'tenant_id' => auth()->user()->id])->first();
        $flatId = Flat::find($this->id);
        $oaIds = DB::table('building_owner_association')->where('building_id',$this->building->id)->pluck('owner_association_id');
        $ownerAssociation = OwnerAssociation::whereIn('id',$oaIds)->pluck('role')->unique();
        $oA = $ownerAssociation->contains('OA');
        $propertyManager = $ownerAssociation->contains('Property Manager');
        $showcheques = FlatTenant::where(['flat_id' => $this->id,'role' => 'Tenant'])->exists();
        $rentalDetails = RentalDetail::where('flat_id', $this->id);
        $chequeoverdue = $rentalDetails->whereHas('rentalCheques', function($query) {
            $query->where('status', 'Overdue');
        })->exists();
        $residingInFlat = $flat?->residing_in_same_flat;
        $bills = Bill::where('flat_id', $this->id)->where('status', 'Overdue')->pluck('type');
        $status = UserApproval::where(['user_id' => auth()->user()->id, 'flat_id' => $this->id])->latest()->first();
        return [
            'flat_name' => $this->property_number,
            'flat_id' => $this->id,
            'building_name' => $this->building->name,
            'building_slug' => $this->building->slug,
            'building_id' => $this->building->id,
            'oa' => $oA,
            'propertymanager' => $propertyManager,
            'role' => $flat?->role,
            'showcheques' => $showcheques,
            'chequeoverdue' => $chequeoverdue,
            'bills' => $bills,
            'status' => $status?->status,
            'remarks' => $status?->remarks,
            'residing_in_flat' => $residingInFlat,
            'oa_logo' => $flatId->ownerAssociation?->profile_photo ? env('AWS_URL').'/'.$flatId->ownerAssociation?->profile_photo : null,
            'building_logo' => $this->building->cover_photo ? env('AWS_URL').'/'.$this->building->cover_photo : null,
        ];
    }
}
