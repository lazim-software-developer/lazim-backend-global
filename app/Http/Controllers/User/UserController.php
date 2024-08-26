<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\User\UserFlatResource;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function fetchUserFlats()
    {
        $user = auth()->user();

        if ($user && $user?->role->name == 'Tenant'){
            $flatIds = FlatTenant::where('tenant_id',$user->id)->where('active', true)->pluck('flat_id');
            $flats = Flat::whereIn('id',$flatIds)->get();
        }
        else{
            $flats = $user->residences->where('active', true);
        }
        return $flats->count() > 0 ? UserFlatResource::collection($flats) : response()->json(['error' => 'Unauthorized'], 401);;
    }

    // List all flats for the logged in user
    public function getUserFlats() {
        // Get the logged-in user's email
        $flats = auth()->user()->flats;
    
        return FlatResource::collection(($flats));
    }

    // List all family members from Residential form
    public function getFamilyMembers(Building $building) {
        return auth()->user()->residentialForm()->where('building_id', $building->id)->where('status', 'approved')->get(['id', 'name']);
    }

    public function deleteUser() 
    {
        $user = User::find(auth()->user()->id);
        $user->update(['active' => false]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'User deleted successfully!',
            'code' => 200,
        ]))->response()->setStatusCode(200); 
    }
}
