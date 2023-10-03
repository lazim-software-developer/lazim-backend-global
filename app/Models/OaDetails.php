<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OaDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'oa_id',
        'user_id'
    ];

    protected $searchableFields = ['*'];

    protected $table = 'oa_details';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
