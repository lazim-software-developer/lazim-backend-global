<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code','category_id'];

    protected $table = 'subcategories';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
