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

    public function getModelNameAttribute()
    {
        return class_basename($this->mediaable_type);
    }
}
