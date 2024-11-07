<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCreateRequest;
use App\Http\Requests\Vendor\ItemManagmentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\ItemsResource;
use App\Models\Item;
use App\Models\ItemInventory;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemsController extends Controller
{
    public function index(Request $request,Vendor $vendor){
       $items = $vendor->items()
                ->when($request->filled('type'), function ($query) use ($vendor, $request) {
                    $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date', '>', now()->toDateString())->unique()
                        ->filter(function($buildings) use($request){
                            return $buildings->ownerAssociations->contains('role',$request->type);
                        });
                    $query->whereIn('building_id', $buildings->pluck('id'));
                });
       return ItemsResource::collection($items->paginate($request->query('count', 10)));
    }

    public function updateItems(ItemManagmentRequest $request,Item $item){

        if($request->type == 'used' && $item->quantity < $request->quantity ){
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'The quantity of the item is less than your requirement!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        $inventory = ItemInventory::create([
            'item_id' => $item->id,
            'date' => $request->date,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'comments' => $request->comment,
            'user_id' => auth()->user()->id,
        ]);

        if ($request->type == 'incoming') {
            $item->quantity = $item->quantity + $request->quantity;
            $item->save();
        }
        if ($request->type == 'used') {
            $item->quantity = $item->quantity - $request->quantity;
            $item->save();
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Item details updated successfully!',
            'status' => 'success',
            'code' => 201,
            'data' => $inventory,
        ]))->response()->setStatusCode(201);
    }

    public function viewItem(Item $item){
        return new ItemsResource($item);
     }
    public function create(Vendor $vendor,ItemCreateRequest $request)
    {
        $data = $request->only(['name','quantity','building_id','description']);
        $data['owner_association_id'] = DB::table('building_owner_association')->where('building_id',$request->building_id)->first()?->owner_association_id;
        $item = Item::create($data);

        $vendor->items()->attach($item->id);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Item created successfully!',
            'status' => 'success',
            'code' => 201,
            'data' => $item,
        ]))->response()->setStatusCode(200);
    }
}
