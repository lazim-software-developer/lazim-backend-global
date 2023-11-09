<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Accounting\SubCategory;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code'];

    public function subcategorys()
    {
        return $this->hasMany(SubCategory::class);
    }
}
