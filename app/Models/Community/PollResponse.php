<?php

namespace App\Models\Community;

use App\Models\Community\Poll;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollResponse extends Model
{
    use HasFactory;

    protected $table = 'poll_responses';

    protected $fillable = ['answer', 'submitted_at', 'polling_id', 'submitted_by'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
