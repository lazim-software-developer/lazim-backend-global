<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Category;
use App\Models\Master\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code', 'category_id'];

    protected $table = 'subcategories';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
