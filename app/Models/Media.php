<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url', 'mediaable_id', 'mediaable_type'];

    public function mediaable()
    {
        return $this->morphTo();
    }
}
