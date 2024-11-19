<?php

namespace App\Http\Controllers;

use App\Http\Requests\FamilyMemberRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\FamilyMemberDetailsResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\FamilyMember;
use App\Models\Master\DocumentLibrary;
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

        if($request->has('others')){
            foreach ($request->others as $file) {
                $path = optimizeDocumentAndUpload($file['file']);
                $family->documents()->create([
                    'name' => 'Other Document',
                    'document_library_id' => DocumentLibrary::where('name', 'Other documents')->first()->id,
                    'building_id' => $building->id,
                    'owner_association_id' => $oa_id,
                    'url' => $path,
                    'status' => 'pending',
                    'documentable_id' => $family->id,
                    'documentable_type' => FamilyMember::class,
                    'flat_id' => $request->flat_id,
                    // 'expiry_date' => $file['expiry_date'],
                ]);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Family member added successfully',
            'code' => 201,
            'status' => 'success',
            'data' => FamilyMemberDetailsResource::make($family),
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
            'data' => FamilyMemberDetailsResource::collection($family),
        ];
    }

    public function update(FamilyMemberRequest $request, FamilyMember $familyMember)
    {
        $familyMember->update($request->all());

        if ($request->has('deleted_files')) {
            Document::whereIn('id', $request->deleted_files)
                ->where(['documentable_id' => $familyMember->id, 'documentable_type' => FamilyMember::class])
                ->delete();
        }

        if($request->has('others')){
            foreach($request->others as $file){
                $path = optimizeDocumentAndUpload($file['file']);
                Document::where(['documentable_id' => $familyMember->id, 'documentable_type' => FamilyMember::class, 'id' => $file['id']])->update([
                    'url' => $path,
                    'status' => 'pending',
                    // 'expiry_date' => $file['expiry_date'],
                ]);
            }
        }

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

    public function show(FamilyMember $familyMember)
    {
        return new FamilyMemberDetailsResource($familyMember);

    }
}
