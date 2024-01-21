<?php

namespace App\Models;

use App\Models\Item;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemInventory extends Model
{
    use HasFactory;
    protected $table = 'item_inventory';
    protected $fillable = ['item_id','date','type','quantity','user_id','comments'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
