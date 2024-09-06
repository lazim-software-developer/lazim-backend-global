<?php

namespace App\Console\Commands;

use App\Models\Forms\MoveInOut;
use App\Models\User\User;
use Illuminate\Console\Command;

class ResidentMoveOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resident-move-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moveouts = MoveInOut::where('type', 'move-out')
            ->where(function ($query) {
                $query->where('moving_date', now()->toDateString())
                    ->where('moving_time', now()->format('H:i'))
                    ->orWhere('moving_date', '<', now()->toDateString());
            })
            ->pluck('user_id');

        $user = User::whereIn('id', $moveouts)->update([
            'active' => false
        ]);
    }
}
