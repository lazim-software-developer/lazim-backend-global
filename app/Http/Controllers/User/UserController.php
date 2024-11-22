<?php

namespace App\Http\Controllers\User;

use App\Models\User\User;
use App\Models\UserApproval;
use Illuminate\Http\Request;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\User\UserFlatResource;
use App\Http\Resources\CustomResponseResource;

class UserController extends Controller
{
    public function fetchUserFlats()
    {
        $user = auth()->user();

        if ($user && $user?->role->name == 'Tenant'){
            $flatIds = FlatTenant::where('tenant_id',$user->id)->where('active', true)->pluck('flat_id');
            $flatIds = UserApproval::where('user_id',$user->id)->where('status','approved')->whereIn('flat_id',$flatIds)->pluck('flat_id');
            $flats = Flat::whereIn('id',$flatIds)->get();
        }
        else{
            $flats = $user->residences;
        }

        if ($flats->isEmpty()) {
            // Handle the case where there are no flats
            return response()->json(['message' => 'No flats available'], 401);
        } else {
            return UserFlatResource::collection($flats);
        }
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

    public function pendingFlats(Request $request)
    {
        $user = auth()->user();
        $flats = UserApproval::where('user_id', $user->id)
            ->whereNull('status')
            ->pluck('flat_id');

        return UserFlatResource::collection($flats);
    }
}
