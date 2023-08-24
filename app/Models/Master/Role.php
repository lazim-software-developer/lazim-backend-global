<?php

namespace App\Models\Master;

use App\Models\User\User;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name'];

    protected $searchableFields = ['*'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    // public function building()
    // {
    //     return $this->belongsTo(Building::class);
    // }

}
