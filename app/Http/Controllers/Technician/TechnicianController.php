<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveRequest;
use App\Http\Requests\Technician\AddTechnicianRequest;
use App\Http\Requests\Technician\ServiceIdRequest;
use App\Http\Requests\Technician\TechnicianIdRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Technician\ListTechnicianResource;
use App\Http\Resources\Technician\ServiceTechnicianResource;
use App\Jobs\AccountCreationJob;
use App\Jobs\TechnicianAccountCreationJob;
use App\Models\Asset;
use App\Models\Building\Complaint;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TechnicianController extends Controller
{
    public function registration(AddTechnicianRequest $request)
    {
        // $vendor= Vendor::where('owner_id', auth()->user()->id)->first();
        // return !(TechnicianAssets::where('asset_id', 1)->where('vendor_id', $vendor->id)->where('active',1)->exists());

        $request->merge([
            'first_name' => $request->name,
            'role_id' => Role::where('name', 'Technician')->first()->id,
            'active'   => true,
            'email_verified' => true,
            'phone_verified' => true,
        ]);

        $user = User::create($request->all());

        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();
        $name = $vendor->name;
        $technician_number = strtoupper(substr($name, 0, 2)) . date('YmdHis');
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

        TechnicianAccountCreationJob::dispatch($user, $password);

        $assets = $vendor->assets->unique();
        foreach ($assets as $asset) {
            if (!(TechnicianAssets::where('asset_id', $asset->id)->where('vendor_id', $vendor->id)->where('active', 1)->exists()) && $asset->service_id == $request->service_id) {
                TechnicianAssets::create([
                    'asset_id' => $asset->id,
                    'technician_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'building_id' => $asset->building_id,
                    'active' => 1,
                ]);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Technician Added Successfully!',
            'message' => "We have sent password to registered email account",
            'code' => 201,
            'status' => 'success',
            'data' => $technician
        ]))->response()->setStatusCode(201);
    }

    public function index(Service $service, Vendor $vendor)
    {

        $technicians = $service->technicianVendors->where('vendor_id', $vendor->id);

        return ServiceTechnicianResource::collection($technicians);
    }

    public function technicianList(Service $service, Vendor $vendor)
    {
        $contract = Contract::where('vendor_id', $vendor->id)
            ->where('service_id', $service->id)->where('end_date', '>=', Carbon::now()->toDateString())->first()?->service_id;
        $serviceTechnician = DB::table('service_technician_vendor')->where('service_id', $contract)->pluck('technician_vendor_id');
        $technicians = TechnicianVendor::whereIn('id', $serviceTechnician)->where('active', true)->where('vendor_id',$vendor->id)->get();
        return ListTechnicianResource::collection($technicians);
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

    public function attachTechnician(ServiceIdRequest $request, TechnicianVendor $technician)
    {

        if ($technician->active == false) {
            return (new CustomResponseResource([
                'title' => 'Technician is not active',
                'message' => "Please active Technician to assign services",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

        $technician->services()->syncWithoutDetaching([$request->service_id]);
        $vendor = Vendor::where('owner_id', auth()->user()->id)->first();
        $assets = $vendor->assets->unique();
        foreach ($assets as $asset) {
            if (!(TechnicianAssets::where('asset_id', $asset->id)->where('vendor_id', $vendor->id)->where('active', 1)->exists()) && $asset->service_id == $request->service_id) {
                TechnicianAssets::create([
                    'asset_id' => $asset->id,
                    'technician_id' => $technician->technician_id,
                    'vendor_id' => $vendor->id,
                    'building_id' => $asset->building_id,
                    'active' => 1,
                ]);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Technician assigned',
            'message' => "Technician assigned to task successfully!",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function assignTechnician(TechnicianIdRequest $request, Complaint $complaint)
    {

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

    public function listTechnicians(ServiceIdRequest $request, Vendor $vendor)
    {
        $assigned = DB::table('service_technician_vendor')->where('service_id', $request->service_id)->pluck('technician_vendor_id');
        $technicians = TechnicianVendor::where('vendor_id', $vendor->id)->where('active', true)->whereNotIn('id', $assigned)->get();
        return ListTechnicianResource::collection($technicians);
    }

    public function fetchTechnicianAssetDetails(Asset $asset)
    {
        // Check if asset assigned for the logged in user
        $technicianAssetData = TechnicianAssets::where(['technician_id' => auth()->user()->id, 'asset_id' => $asset->id, 'active' => 1]);

        if (!$technicianAssetData->exists()) {
            return (new CustomResponseResource([
                'title' => 'Asset is not assigned to you!',
                'message' => ". Please contact admin team for more details!",
                'code' => 400,
                'status' => 'error',
            ]))->response()->setStatusCode(400);
        }

        // Fetch details
        $technicianAsset = $technicianAssetData->first();
        $latestMaintenance = $technicianAsset->assetMaintenances->last();
        $status = 'not-started';
        $id = null;
        $last_date =  null;

        if ($latestMaintenance) {
            $status = $latestMaintenance->status;
            $id = $latestMaintenance->id;
            $last_date = $latestMaintenance->maintenance_date;
        }

        return [
            'id' => $id,
            'technician_asset_id' => $technicianAsset->id,
            'asset_id' => $technicianAsset->asset_id,
            'asset_name' => $technicianAsset->asset->name,
            'maintenance_status' => $status,
            'building_name' => $technicianAsset->building->name,
            'building_id' => $technicianAsset->building->id,
            'location' => $technicianAsset->asset->location,
            'description' => $technicianAsset->asset->description,
            'last_service_on' => $last_date
        ];
    }
}
