<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\AddTechnicianRequest;
use App\Models\Master\Role;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function registration(AddTechnicianRequest $request) {
        
        $request->merge([
            'role_id' => Role::where('name', 'Technician')->first()->id,
        ]);
        $user = User::create($request->all());
        $vendorId = Vendor::where('owner_id',auth()->user()->id)->first()->id;

        $technician = TechnicianVendor::create([
            'technician_id'  => $user->id,
            'vendor_id'      => $vendorId,
            'active'         => true,
            'position'       => $request->position
        ]);
    }
}
