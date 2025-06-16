<?php

namespace App\Http\Controllers\AccessCard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccessCard\EmirateOfRegistrationResource;

class AccessCardCommanController extends Controller
{
    public function emirateOfRegistration() {
        $emirates = DB::table('emirate_of_registrations')->select('id', 'name','status')->where('status', 1)->get();
        return EmirateOfRegistrationResource::collection($emirates);
    }
}
