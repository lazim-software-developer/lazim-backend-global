<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserFlatResource;

class UserController extends Controller
{
    public function fetchUserFlats()
    {
        $user = auth()->user();

        // Assuming you have a relationship defined in the User model for flats
        $flats = $user->residences;

        return UserFlatResource::collection($flats);
    }
}
