<?php

namespace App\Http\Controllers\Api\OwnerAssociation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\User\UserFlatResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\User\User;

class UserController extends Controller
{
    public function fetchUserFlats()
    {
        $user = auth()->user();

        if ($user && $user?->role->name == 'Tenant') {
            $flatIds = FlatTenant::where('tenant_id', $user->id)->where('active', true)->pluck('flat_id');
            $flats = Flat::whereIn('id', $flatIds)->get();
        } else {
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
    public function getUserFlats()
    {
        // Get the logged-in user's email
        $flats = auth()->user()->flats;

        return FlatResource::collection(($flats));
    }

    // List all family members from Residential form
    public function getFamilyMembers(Building $building)
    {
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
