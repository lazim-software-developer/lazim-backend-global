<?php

namespace App\Models\Building;
use App\Models\User\User;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\Master\DocumentLibrary;
use App\Models\Media;
use App\Models\OaUserRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'document_library_id',
        'owner_association_id',
        'url',
        'status',
        'comments',
        'expiry_date',
        'accepted_by',
        'documentable_id',
        'documentable_type',
        'name'
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'comments' => 'array',
        'expiry_date' => 'date',
    ];

    public function documentLibrary()
    {
        return $this->belongsTo(DocumentLibrary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
    public function documentUsers()
    {
        return $this->belongsTo(User::class, 'documentable_id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function documentable()
    {
        return $this->morphTo();
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
