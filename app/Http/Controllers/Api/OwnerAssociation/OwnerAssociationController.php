<?php
namespace App\Http\Controllers\Api\OwnerAssociation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\OwnerAssociationRepository;
use App\Http\Requests\OwnerAssociation\StoreRequest;
use App\Http\Requests\OwnerAssociation\UpdateRequest;
use App\Http\Resources\OwnerAssociation\OwnerAssociationResource;

class OwnerAssociationController extends Controller
{
    private $repository;

    public function __construct(OwnerAssociationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $this->repository->list($request);
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching owner associations'], 500);
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $this->repository->store($request->validated());
            return response()->json(['data' => new OwnerAssociationResource($data), 'message' => 'Owner Association created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating owner association'], 500);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $data = $this->repository->update($id, $request->validated());
            return response()->json(['data' =>  new OwnerAssociationResource($data), 'message' => 'Owner Association updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating owner association'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return response()->json(['message' => 'Owner Association deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting owner association'], 500);
        }
    }

    public function changeStatus($id)
    {
        try {
            $data = $this->repository->changeStatus($id);
            return response()->json(['data' => $data, 'message' => 'Status updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error changing status'], 500);
        }
    }
    // app/Http/Controllers/Api/OwnerAssociationController.php

public function show($id)
{
    try {
        $ownerAssociation = $this->repository->show($id);
        
        // Using Resource to transform the data
        return response()->json([
            'status' => true,
            'data' => new OwnerAssociationResource($ownerAssociation),
            'message' => 'Owner Association details retrieved successfully'
        ], 200);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Owner Association not found'
        ], 404);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error retrieving owner association details',
            'error' => $e->getMessage()
        ], 500);
    }
}
}