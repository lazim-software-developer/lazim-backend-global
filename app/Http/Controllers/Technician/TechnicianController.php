<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveRequest;
use App\Http\Requests\EditTechnicianRequest;
use App\Http\Requests\Technician\AddTechnicianRequest;
use App\Http\Requests\Technician\ServiceIdRequest;
use App\Http\Requests\Technician\TechnicianIdRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\ListAllTechnicianResource;
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
use Vinkla\Hashids\Facades\Hashids;

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
        $technician_number = strtoupper(substr($name, 0, 2)) . Hashids::encode($user->id);
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

        $technicians = $service->technicianVendors->where('vendor_id', $vendor->id)->where('active',true);

        return ServiceTechnicianResource::collection($technicians->paginate(10));
    }

    public function edit(EditTechnicianRequest $request, User $technician)
    {
        if(isset($request->name)){
            $request->merge([
                'first_name' => $request->name
            ]);
        }
        $technician->update($request->all());

        return (new CustomResponseResource([
            'title' => 'Details updated!',
            'message' => "Technician deatils updated successfully!",
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }

    public function technicianList(Service $service, Vendor $vendor)
    {
        $contract = Contract::where('vendor_id', $vendor->id)
            ->where('service_id', $service->id)->where('end_date', '>=', Carbon::now()->toDateString())->first()?->service_id;
        $serviceTechnician = DB::table('service_technician_vendor')->where('service_id', $contract)->pluck('technician_vendor_id');
        $technicians = TechnicianVendor::whereIn('id', $serviceTechnician)->where('active', true)->where('vendor_id', $vendor->id)->get();
        return ListTechnicianResource::collection($technicians);
    }

    public function activeDeactive(ActiveRequest $request, TechnicianVendor $technician)
    {
        if (!$request->active && Complaint::where('technician_id', $technician?->technician_id)->where('status', 'open')->exists()) {
            return (new CustomResponseResource([
                'title' => 'Technician cannot be deactivated!',
                'message' => "Technician has pending tasks, cannot deactivate!",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }
        $technician->active = $request->active;
        $technician->save();
        $user = User::find($technician?->technician_id)->update([
            'active' => $request->active
        ]);
        return (new CustomResponseResource([
            'title' => 'active status updated',
            'message' => "Technician status updated!",
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
        // Check if the service is already attached but inactive
        $existingService = $technician->services()->wherePivot('service_id', $request->service_id)->first();

        if ($existingService) {
            // If the service exists, update the pivot table to set active to true
            $technician->services()->updateExistingPivot($request->service_id, ['active' => true]);
        } else {
            // If the service does not exist, sync without detaching
            $technician->services()->syncWithoutDetaching([$request->service_id]);
        }

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

    public function detachTechnician(ServiceIdRequest $request, TechnicianVendor $technician)
    {

        $technician->services()->updateExistingPivot($request->service_id, ['active' => false]);


        return (new CustomResponseResource([
            'title' => 'Technician detached',
            'message' => "Technician deatched from current service!",
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
        $technicians = TechnicianVendor::where('vendor_id', $vendor->id)->where('technician_vendors.active', true)->whereNotIn('technician_vendors.id', $assigned)->get();
        return ListTechnicianResource::collection($technicians);
    }

    public function allTechnician(Vendor $vendor){
        $technicians = TechnicianVendor::where('vendor_id', $vendor->id);
        return ListAllTechnicianResource::collection($technicians->paginate(10));
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

        $maintenanceDate = new Carbon($latestMaintenance?->maintenance_date);  //changed line

        // Add the number of days specified by $asset->frequency_of_service
        $nextMaintenanceDue = $maintenanceDate->addDays($asset->frequency_of_service);
        if ($latestMaintenance) {
            if ($nextMaintenanceDue > now()) {
                $status = $latestMaintenance->status;
                $id = $latestMaintenance->id;
                $last_date = $latestMaintenance->maintenance_date;
            } else {
                $id = $latestMaintenance->id;
                $last_date = $latestMaintenance->maintenance_date;
            }
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
