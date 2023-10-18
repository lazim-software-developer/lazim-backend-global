<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserFlatResource;

class UserController extends Controller
{
    public function fetchUserFlats()
    {
        $user = auth()->user();

        $flats = $user->residences;

        return UserFlatResource::collection($flats);
    }
}
