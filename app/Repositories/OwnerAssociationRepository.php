<?php
namespace App\Repositories;

use App\Models\OwnerAssociation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class OwnerAssociationRepository
{
    private $model;

    public function __construct(OwnerAssociation $model)
    {
        $this->model = $model;
    }

    public function list($request)
    {
        return $this->model
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('oa_number', 'like', "%{$request->search}%");
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');
    }

    public function store($data)
    {
        $user = auth()->user();
        $data['slug'] = rand(1000,9999).Str::slug($data['name']);
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        $data['verified'] = 1;
        $data['verified_by'] = 1;
        
        // Handle file uploads
        // Handle file uploads
        $uploadFields = ['trn_certificate', 'trade_license', 'chamber_document', 'memorandum_of_association', 'logo'];
        foreach ($uploadFields as $field) {
            if (isset($data[$field])) {
                    $image = $data[$field];
                    $imagePath = imageUploadonS3($image, 'owner-associations/'.$field);
                    $data[$field] = $imagePath;
            }
        }
        if(!empty($data['logo'])){
            $data['profile_photo']= $data['logo'];
        }
        if(!empty($data['chamber_document'])){
            $data['dubai_chamber_document']= $data['chamber_document'];
        }
        if(!empty($data['memorandum_of_association'])){
            $data['memorandum_of_association']= $data['memorandum_of_association'];
        }

        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $association = $this->model->findOrFail($id);
        $user = auth()->user();
        $data['updated_by'] = $user->id;
        
        // Handle file uploads
        $uploadFields = ['trn_certificate', 'trade_license', 'chamber_document', 'memorandum_of_association', 'logo'];
        foreach ($uploadFields as $field) {
            if (isset($data[$field])) {
                    $image = $data[$field];
                    $imagePath = imageUploadonS3($image, 'owner-associations/'.$field);
                    $data[$field] = $imagePath;
            }
        }
        if(!empty($data['logo'])){
            $data['profile_photo']= $data['logo'];
        }
        if(!empty($data['chamber_document'])){
            $data['dubai_chamber_document']= $data['chamber_document'];
        }
        if(!empty($data['memorandum_of_association'])){
            $data['memorandum_of_association']= $data['memorandum_of_association'];
        }
        $association->update($data);
        return $association;
    }

    public function delete($id)
    {
        $association = $this->model->findOrFail($id);
        $association->delete();
        return true;
    }

    public function changeStatus($id)
    {
        $association = $this->model->findOrFail($id);
        $association->active = $association->active == 1 ? 0 : 1;
        $association->save();
        return $association;
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