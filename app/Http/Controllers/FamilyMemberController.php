<?php

namespace App\Http\Controllers;

use App\Http\Requests\FamilyMemberRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyMemberController extends Controller
{
    public function store(FamilyMemberRequest $request, Building $building)
    {
        $userId = auth()->user()->id;
        $oa_id = DB::table('building_owner_association')->where('building_id', $building->id)->where('active', true)->first()?->owner_association_id;
        $request->merge([
            'user_id' => $userId,
            'owner_association_id' => $oa_id,
            'building_id' => $building->id
        ]);

        $family = FamilyMember::create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Family member added successfully',
            'code' => 201,
            'status' => 'success',
            'data' => $family,
        ]))->response()->setStatusCode(201);
    }

    public function index(Request $request,Building $building, $unit = null)
    {
        Log::info($unit);
        Log::info($request->unit);
        $userId = auth()->user()?->id;

        $oa_id = DB::table('building_owner_association')->where('building_id', $building->id)->where('active', true)->first()?->owner_association_id;

        $familyQuery = FamilyMember::where('user_id', $userId)->where(['owner_association_id' => $oa_id, 'building_id' => $building->id]);

        if($unit) {
            $familyQuery->where('flat_id', $request->unit);
        }

        $family = $familyQuery->get();
        return [
            'data' => $family,
        ];
    }
}
