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
        $userId = auth()->user()?->id;

        $oa_id = DB::table('building_owner_association')->where('building_id', $building->id)->where('active', true)->first()?->owner_association_id;

        $familyQuery = FamilyMember::where('user_id', $userId)->where(['owner_association_id' => $oa_id, 'building_id' => $building->id,'active'=>true]);

        if($request->unit) {
            $familyQuery->where('flat_id', $request->unit);
        }

        $family = $familyQuery->get();
        return [
            'data' => $family,
        ];
    }

    public function update(FamilyMemberRequest $request, FamilyMember $familyMember)
    {
        $familyMember->update($request->all());
        $familyMember->save();

        return (new CustomResponseResource([
            'title' => 'Updated Successfully',
            'message' => 'Family Member Updated Successfully',
            'code' => 200,
            'status' => 'success'
        ]))->response()->setStatusCode(200);

    }

    public function delete(FamilyMember $familyMember)
    {
        if (!$familyMember) {
            return (new CustomResponseResource([
                'title' => 'Not Found',
                'message' => 'Family member not found',
                'code' => 404,
                'status' => 'error',
            ]))->response()->setStatusCode(404);
        }
        $familyMember->active = false;
        $familyMember->save();
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Family member deactivated successfully',
            'code' => 200,
            'status' => 'success',
        ]))->response()->setStatusCode(200);
    }
}
