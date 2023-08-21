<?php

namespace App\Models\Building;
use App\Models\Master\DocumentLibrary;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'document_library_id',
        'url',
        'status',
        'comments',
        'expiry_date',
        'accepted_by',
        'documentable_id',
        'documentable_type',
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

    public function documentable()
    {
        return $this->morphTo();
    }
}
