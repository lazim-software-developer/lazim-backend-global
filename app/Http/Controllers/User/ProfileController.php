<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UploadProfilePictureRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\User\ProfileResource;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        return new ProfileResource($user);
    }

    // Update profile details
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $user->update($request->only('first_name', 'last_name'));

        return new CustomResponseResource([
            'title' => 'Profile Updated',
            'message' => 'Your profile has been updated successfully.'
        ]);
    }

    // Upload profile picture
    public function uploadPicture(UploadProfilePictureRequest $request)
    {
        $user = auth()->user();

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imagePath = optimizeAndUpload($image, 'dev/profile_pictures');

            // Assuming you have a column `profile_picture` in your users table
            $user->profile_photo = $imagePath;
            $user->save();
        }

        return new CustomResponseResource([
            'title' => 'Profile Picture Updated',
            'message' => 'Your profile picture has been updated successfully.',
        ]);
    }

    // Change Password
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        // Check if the provided current password matches the one in the database
        if (!Hash::check($request->current_password, $user->password)) {
            return (new CustomResponseResource([
                'title' => 'Password Update Failed',
                'message' => 'The provided current password does not match our records.',
                'code' => 422,
                'status' => 'error',
            ]))->response()->setStatusCode(422);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return new CustomResponseResource([
            'title' => 'Password Updated',
            'message' => 'Your password has been updated successfully.',
        ]);
    }
}
