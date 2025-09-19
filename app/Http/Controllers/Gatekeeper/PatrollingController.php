<?php

namespace App\Http\Controllers\Gatekeeper;

use Carbon\Carbon;
use App\Models\Floor;
use Illuminate\Http\Request;
use App\Models\Building\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Building\BuildingPoc;
use App\Models\Gatekeeper\Patrolling;
use App\Models\Gatekeeper\PatrollingList;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponseResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\GateKeeper\FloorResource;
use App\Http\Resources\GateKeeper\PatrollingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatrollingController extends Controller
{
    public function fetchAllFloors(Building $building): AnonymousResourceCollection|JsonResponse
    {
        $context = ['user_id' => auth()->user()?->id, 'building_id' => $building->id];
        if (!$building) {
            return $this->errorResponse('Building not found.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isUserAuthorized($building)) {
            return $this->errorResponse('You are not authorized to patrol this building.', Response::HTTP_FORBIDDEN);
        }
        $oaId = $this->getOwnerAssociationId($building);
        if (!$oaId) {
            return $this->errorResponse('No active owner association found for this building.', Response::HTTP_BAD_REQUEST);
        }
        try {
            $query = Patrolling::where('building_id', $building->id)
                ->where('owner_association_id', $oaId)
                ->latest()
                ->paginate(10);
            return PatrollingResource::collection($query);
        } catch (\Exception $e) {
            Log::error('Failed to create patrolling record', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('An unexpected error occurred while creating the patrolling record.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    // Start patrolling API
    /**
     * Store a new patrolling record.
     *
     * @param Request $request
     * @param Building $building
     * @return JsonResponse
     */
    public function createPatrolling(Building $building): JsonResponse
    {
        $context = ['user_id' => auth()->user()?->id, 'building_id' => $building->id];
        if (!$building) {
            return $this->errorResponse('Building not found.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->isUserAuthorized($building)) {
            return $this->errorResponse('You are not authorized to patrol this building.', Response::HTTP_FORBIDDEN);
        }

        $oaId = $this->getOwnerAssociationId($building);
        if (!$oaId) {
            return $this->errorResponse('No active owner association found for this building.', Response::HTTP_BAD_REQUEST);
        }
        $floorIds = DB::table('location_qr_codes')
            ->where('building_id', $building->id)
            ->pluck('id');

        if ($floorIds->isEmpty()) {
            return $this->errorResponse('No floors found for this building.', Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // $patrolling = $this->createPatrollingRecord($building, $oaId);
            $patrollingData = new PatrollingResource($this->createPatrollingRecord($building, $oaId));
            DB::commit();
            return $this->successResponse('Patrolling record created successfully!', Response::HTTP_CREATED, $patrollingData);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create patrolling record', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('An unexpected error occurred while creating the patrolling record.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Store a new patrolling record.
     *
     * @param Request $request
     * @param Building $building
     * @return JsonResponse
     */
    public function store(Request $request, Building $building): JsonResponse
    {
        $context = ['user_id' => auth()->user()?->id, 'building_id' => $building->id, 'request_data' => $request->all()];
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|integer|exists:location_qr_codes,id',
            'location_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        if (!$this->isUserAuthorized($building)) {
            return $this->errorResponse('You are not authorized to patrol this building.', Response::HTTP_FORBIDDEN);
        }

        $oaId = $this->getOwnerAssociationId($building);
        if (!$oaId) {
            return $this->errorResponse('No active owner association found for this building.', Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $patrollingRecord = $this->getPatrollingRecord($building, $oaId);

            if (!$patrollingRecord) {
                return $this->errorResponse('No patrolling list entry found. Start New Patrolling Session', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $update = $this->updatePatrollingList($patrollingRecord, $request->location_id, $request->location_name);
            DB::commit();
            return $update;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process patrolling request', [
                ...$context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('An unexpected error occurred while processing the patrolling request.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if the authenticated user is authorized to patrol the building.
     *
     * @param Building $building
     * @return bool
     */
    private function isUserAuthorized(Building $building): bool
    {
        return BuildingPoc::where('building_id', $building->id)
            ->where('active', true)
            ->first()?->user_id === auth()->user()?->id;
    }

    /**
     * Get the owner association ID for the building.
     *
     * @param Building $building
     * @return int|null
     */
    private function getOwnerAssociationId(Building $building): ?int
    {
        return DB::table('building_owner_association')
            ->where('building_id', $building->id)
            ->where('active', true)
            ->value('owner_association_id');
    }

    /**
     * Get patrolling record for the building.
     *
     * @param Building $building
     * @param int $oaId
     * @param int $floorId
     * @param string|null $locationName
     * @return Patrolling
     */
    private function getPatrollingRecord(Building $building, int $oaId): Patrolling|null
    {
        $patrollingRecord = Patrolling::where('building_id', $building->id)
            ->where('owner_association_id', $oaId)
            ->where('is_completed', 0)
            ->first();

        if (!$patrollingRecord) {
            // return $this->startNewPatrolling($building, $oaId); // need to change after application is ready
            return null;
        }

        return $patrollingRecord;

    }
    /**
     * Get or create a patrolling record for the building.
     *
     * @param Building $building
     * @param int $oaId
     * @param int $floorId
     * @param string|null $locationName
     * @return Patrolling
     */
    private function createPatrollingRecord(Building $building, int $oaId): Patrolling
    {
        $patrollingRecord = Patrolling::where('building_id', $building->id)
            ->where('owner_association_id', $oaId)
            ->where('is_completed', 0)
            ->first();

        if (!$patrollingRecord) {
            $patrollingRecord = $this->startNewPatrolling($building, $oaId);
        }

        return $patrollingRecord;
    }

    /**
     * Start a new patrolling session.
     *
     * @param Building $building
     * @param int $oaId
     * @param int $floorId
     * @param string|null $locationName
     * @return Patrolling
     */
    private function startNewPatrolling(Building $building, int $oaId): Patrolling
    {
        $floorIds = DB::table('location_qr_codes')
            ->where('building_id', $building->id)
            ->pluck('id');

        $totalFloors = $floorIds->count();

        $patrollingRecord = Patrolling::create([
            'building_id' => $building->id,
            'owner_association_id' => $oaId,
            'is_completed' => 0,
            'patrolled_by' => auth()->user()?->id,
            'total_count' => $totalFloors,
            'completed_count' => 0,
            'pending_count' => $totalFloors,
            'started_at' => now(),
        ]);

        $this->createPatrollingListEntries($building, $patrollingRecord, $floorIds);

        return $patrollingRecord;
    }

    /**
     * Create entries in the patrolling list for each floor.
     *
     * @param Building $building
     * @param Patrolling $patrollingRecord
     * @param \Illuminate\Support\Collection $floorIds
     * @param int $currentFloorId
     * @param string|null $locationName
     * @return void
     */
    private function createPatrollingListEntries(Building $building, Patrolling $patrollingRecord, $floorIds): void
    {
        foreach ($floorIds as $floorId) {
            $locationDetails = DB::table('location_qr_codes')->where('building_id', $building->id)->where('id', $floorId)->first();
            Log::info('Creating patrolling list entry for floor', [
                'building_id' => $building->id,
                'floor_id' => $locationDetails->id,
                'location_name' => $locationDetails->floor_name,
                'patrolling_record_id' => $patrollingRecord->id,
            ]);
            PatrollingList::create([
                'patrolling_record_id' => $patrollingRecord->id,
                'floor_id' => $locationDetails->floor_id,
                'location_id' => $locationDetails->id,
                'location_name' => $locationDetails->floor_name ?? null,
                'is_completed' => 0,
                'patrolled_by' => auth()->user()?->id,
                'building_id' => $building->id,
                'owner_association_id' => $building->owner_association_id,
                'patrolled_at' => null,
            ]);
        }
    }

    /**
     * Update the patrolling list entry for the given floor.
     *
     * @param Patrolling $patrollingRecord
     * @param int $floorId
     * @param string|null $locationName
     * @return JsonResponse
     */
    private function updatePatrollingList(Patrolling $patrollingRecord, int $locationId, ?string $locationName): JsonResponse
    {
        $patrollingList = PatrollingList::where('patrolling_record_id', $patrollingRecord->id)
            ->where('location_id', $locationId)
            ->first();

        if (!$patrollingList) {
            return $this->errorResponse('No patrolling list entry found for this floor.', Response::HTTP_BAD_REQUEST);
        }
        if ($patrollingList->is_completed) {
            return $this->errorResponse('This floor has already been patrolled in the current session.', Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $patrollingList->update([
                'is_completed' => 1,
                'patrolled_at' => now(),
                'patrolled_by' => auth()->user()?->id,
            ]);

            $this->updatePatrollingRecordStatus($patrollingRecord);
            DB::commit();

            return $this->successResponse('Patrolling record updated successfully!', Response::HTTP_CREATED);
        } catch (\Exception $e) {

            DB::rollBack();
            return $this->errorResponse('Failed to update patrolling record.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the patrolling record status if all floors are completed.
     *
     * @param Patrolling $patrollingRecord
     * @return void
     */
    private function updatePatrollingRecordStatus(Patrolling $patrollingRecord): void
    {
        $incompleteCount = PatrollingList::where('patrolling_record_id', $patrollingRecord->id)
            ->where('is_completed', 0)
            ->count();

        if ($incompleteCount === 0) {
            $patrollingRecord->update(['is_completed' => 1, 'ended_at' => now(), 'pending_count' => 0, 'completed_count' => $patrollingRecord->total_count]);
        } else {
            $patrollingRecord->update([
                'completed_count' => PatrollingList::where('patrolling_record_id', $patrollingRecord->id)
                    ->where('is_completed', 1)
                    ->count(),
                'pending_count' => $incompleteCount,
            ]);
        }
    }

    /**
     * Return a standardized error response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $code): JsonResponse
    {
        return (new CustomResponseResource([
            'title' => 'Error',
            'message' => $message,
            'code' => $code,
            'status' => 'error',
        ]))->response()->setStatusCode($code);
    }

    /**
     * Return a standardized success response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    private function successResponse(string $message, int $code, $data = null, ): JsonResponse
    {
        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => $message,
            'code' => $code,
            'data' => $data,
            'status' => 'success',
        ]))->response()->setStatusCode($code);
    }

}
