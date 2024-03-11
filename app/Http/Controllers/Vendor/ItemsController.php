<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\ItemManagmentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Vendor\ItemsResource;
use App\Models\Item;
use App\Models\ItemInventory;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function index(Request $request,Vendor $vendor){
       $items=$vendor->items();
       return ItemsResource::collection($items->paginate($request->query('page', 10)));
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
}
