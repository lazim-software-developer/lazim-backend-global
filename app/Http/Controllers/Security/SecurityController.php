<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Building\Building;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function fetchSecurity(Building $building) {
        return $building->buildingPocs()->where([
            'role_name' => 'security',
            'active' => 1,
            'emergency_contact' => 1
        ])->first()?->user->phone ?? 'NA';
    }
}
