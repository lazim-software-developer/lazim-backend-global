<?php
namespace App\Repositories;

use Illuminate\Support\Str;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FlatRepository
{
    private $model;

    public function __construct(Flat $model)
    {
        $this->model = $model;
    }

    public function list($request)
    {
        return $this->model
            ->leftJoin('buildings', 'flats.building_id', '=', 'buildings.id')
            ->leftJoin('owner_associations', 'flats.owner_association_id', '=', 'owner_associations.id')
            ->select('flats.*', 'buildings.name as building_name', 'owner_associations.name as owner_associations_name')
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('oa_number', 'like', "%{$request->search}%");
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');
    }

    public function store($data)
    {
        $user = auth()->user();
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $building = $this->model->findOrFail($id);
        $user = auth()->user();
        $data['updated_by'] = $user->id;
        $building->update($data);
        return $building;
    }

    public function delete($id)
    {
        $flat = $this->model->findOrFail($id);
        DB::transaction(function () use ($flat) {
            // Delete child records first
            $flat->flatOwners()->delete();  // Assuming you have a relationship defined
            // Then delete the flat
            $flat->delete();
        });
        $flat->delete();
        return true;
    }

    public function changeStatus($id)
    {
        $building = $this->model->findOrFail($id);
        $building->status = $building->status === 1 ? 0 : 1;
        $building->save();
        return $building;
    }

    private function uploadFile($file, $type)
    {
        $path = "owner-associations/{$type}";
        return $file->store($path, 'public');
    }
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }
}