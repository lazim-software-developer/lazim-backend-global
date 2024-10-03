<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlatVisitorResource;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlatVisitorController extends Controller
{
    public function index(Vendor $vendor)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
            ->whereIn('owner_association_id', $ownerAssociationIds)->pluck('building_id');

        $flatVisitors = FlatVisitor::whereIn('building_id', $buildingIds)->where('type','visitor');

        return FlatVisitorResource::collection($flatVisitors->paginate(10));
    }
}
