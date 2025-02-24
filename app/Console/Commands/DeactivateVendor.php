<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeactivateVendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deactivate-vendor';

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
        $vendors = DB::table('building_vendor')->where('end_date','<=',now()->toDateString())->get();
        foreach($vendors as $vendor){
            DB::table('building_vendor')->where('id', $vendor->id)->update(['active' => false]);
        }
    }
}
