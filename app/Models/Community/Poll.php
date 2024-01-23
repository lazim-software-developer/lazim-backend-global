<?php

namespace App\Models\Community;

use App\Models\Building\Building;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $table = 'polls';

    protected $fillable = ['question', 'options', 'status', 'scheduled_at', 'ends_on', 'building_id', 'created_by', 'active'];

    protected $casts = ['options' => 'array'];

    protected $dates = ['scheduled_at'];

    public function getEndsOnDiffAttribute()
    {
        return Carbon::parse($this->attributes['ends_on'])->diffForHumans();
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses() {
        return $this->hasMany(PollResponse::class);
    }
}
