<?php

namespace App\Models\Master;

use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentLibrary extends Model
{
    use HasFactory;
    use Searchable;

    protected $connection = 'mysql';

    protected $fillable = ['name','url','type', 'label'];

    protected $searchableFields = ['*'];

    protected $table = 'document_libraries';

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function building()
    {
        return $this->belongsToMany(Building::class, 'building_documentlibraries','documentlibrary_id','building_id');
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

}
