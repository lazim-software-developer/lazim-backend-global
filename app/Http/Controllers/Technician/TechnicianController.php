<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveRequest;
use App\Http\Requests\Technician\AddTechnicianRequest;
use App\Http\Requests\Technician\ServiceIdRequest;
use App\Http\Requests\Technician\TechnicianIdRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Technician\ServiceTechnicianResource;
use App\Jobs\AccountCreationJob;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TechnicianController extends Controller
{
    public function registration(AddTechnicianRequest $request)
    {

        $request->merge([
            'first_name' => $request->name,
            'role_id' => Role::where('name', 'Technician')->first()->id,
        ]);

        $user = User::create($request->all());

        $vendor= Vendor::where('owner_id', auth()->user()->id)->first();
        $name = $vendor->name;
        $technician_number = strtoupper(substr($name, 0, 2)).date('YmdHis');
        $password = Str::random(12);
        $user->password = Hash::make($password);
        $user->save();

        $technician = TechnicianVendor::create([
            'technician_number' => $technician_number,
            'technician_id'  => $user->id,
            'vendor_id'      => $vendor->id,
            'active'         => true,
            'position'       => $request->position
        ]);

        $technician->services()->syncWithoutDetaching([$request->service_id]);

        AccountCreationJob::dispatch($user, $password);

        return (new CustomResponseResource([
            'title' => 'Technician Added Successfully!',
            'message' => "We have sent password to registered email account",
            'code' => 201,
            'status' => 'success',
            'data' => $technician
        ]))->response()->setStatusCode(201);
    }

    public function index(Service $service)
    {

        $technicians = $service->technicianVendors;

        return ServiceTechnicianResource::collection($technicians);
    }

    public function activeDeactive(ActiveRequest $request, TechnicianVendor $technician)
    {
        $technician->active = $request->active;
        $technician->save();

        return (new CustomResponseResource([
            'title' => 'active status updated',
            'message' => "",
            'code' => 200,
            'status' => 'success',
            'data' => $technician
        ]))->response()->setStatusCode(200);
    }

    public function attachTechnician(ServiceIdRequest $request, TechnicianVendor $technician){

        if ($technician->active == false) {
            return (new CustomResponseResource([
                'title' => 'Technician is not active',
                'message' => "Please active Technician to assign services",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        $technician->services()->syncWithoutDetaching([$request->service_id]);

        return (new CustomResponseResource([
            'title' => 'Technician assigned',
            'message' => "Technician assigned to task successfully!",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function assignTechnician(TechnicianIdRequest $request,Complaint $complaint){

        $complaint->technician_id = $request->technician_id;
        $complaint->save();
        
        return (new CustomResponseResource([
            'title' => 'Technician assigned',
            'message' => "Technician assigned to complaint successfully!",
            'code' => 200,
            'status' => 'success',
            'data' => $complaint
        ]))->response()->setStatusCode(200);
    }
}
