<?php

namespace App\Models\Master;

use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentLibrary extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name','building_id','url','type'];

    protected $searchableFields = ['*'];

    protected $table = 'document_libraries';

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    // public function building()
    // {
    //     return $this->belongsTo(Building::class);
    // }
}
