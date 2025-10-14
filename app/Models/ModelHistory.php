<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Traits\HasHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelHistory extends Model
{
    use HasFactory;

    protected $table = 'model_histories';

    protected $fillable = [
        'historable_type',
        'historable_id',
        'user_id',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function historable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
