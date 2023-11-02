<?php

namespace App\Jobs\Building;

use App\Models\ApartmentOwner;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AssignFlatsToTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function handle()
    {
        // Fetch the owner using the provided email
        $owner = ApartmentOwner::where('email', $this->email)->first();

        $user = User::where('email', $this->email)->first();

        if (!$owner) {
            // No owner found with the given email
            return;
        }

        // Fetch all flats that match the owner's email
        $flats = DB::table('flat_owner')->where('owner_id', $owner->id)
            ->join('flats', 'flats.id', 'flat_owner.flat_id')
            ->selectRaw('MAX(flats.id) as flat_id')
            ->groupBy('flats.building_id', 'flats.property_number')
            ->get();

        foreach ($flats as $flat) {
            // Add an entry in the flat_tenant table for each flat
            $flatDetails = Flat::find($flat->flat_id);
            FlatTenant::updateOrCreate(
                ['tenant_id' => $user->id, 'flat_id' => $flatDetails->id],
                [
                    'tenant_id' => $user->id,
                    'flat_id' => $flatDetails->id,
                    'building_id' => $flatDetails->building_id,
                    'owner_association_id' => $flatDetails->owner_association_id,
                    'start_date' => now(),
                    'active' => 1
                ]
            );
        }
    }
}
