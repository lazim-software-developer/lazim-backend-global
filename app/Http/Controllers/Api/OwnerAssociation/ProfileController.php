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
    /**
     * @api {get} /user/profile Get Authenticated User Profile
     * @apiDescription
     * Retrieves the authenticated user's profile information, including basic details, 
     * verification statuses, associated documents, and related building (for security roles).
     *  
     * **Authentication Required:** Yes (Sanctum)
     *
     * @apiHeader {String} Authorization Bearer token required.
     * @apiHeader {String} Accept application/json
     *
     * @apiExample {curl} Example Request:
     * curl -X GET "https://your-domain.com/api/user/profile" \
     * -H "Authorization: Bearer {token}" \
     * -H "Accept: application/json"
     *
     * @apiSuccess (200 OK) {Integer} id User ID.
     * @apiSuccess (200 OK) {String} first_name User's first name.
     * @apiSuccess (200 OK) {String} last_name User's last name.
     * @apiSuccess (200 OK) {String} email User's email address.
     * @apiSuccess (200 OK) {String} phone User's phone number.
     * @apiSuccess (200 OK) {String|null} profile_pic Public URL to the user's profile photo (S3), or `null` if not uploaded.
     * @apiSuccess (200 OK) {Boolean} email_verified Whether the user's email has been verified.
     * @apiSuccess (200 OK) {Boolean} phone_verified Whether the user's phone has been verified.
     * @apiSuccess (200 OK) {Boolean} active Whether the user account is active.
     * @apiSuccess (200 OK) {String} lazim_id Lazim system ID assigned to the user.
     * @apiSuccess (200 OK) {Integer} role_id User's role ID.
     * @apiSuccess (200 OK) {Integer|null} owner_association_id Associated Owner Association ID (if applicable).
     * @apiSuccess (200 OK) {String} created_at Timestamp when the user was created.
     * @apiSuccess (200 OK) {String} updated_at Timestamp when the user was last updated.
     * @apiSuccess (200 OK) {String|null} remember_token User's remember token.
     * @apiSuccess (200 OK) {String="globalOa"} selectType Indicates the user's OA selection type.
     * @apiSuccess (200 OK) {Boolean} passport Whether a passport document has been uploaded.
     * @apiSuccess (200 OK) {Boolean} visa Whether a visa document has been uploaded.
     * @apiSuccess (200 OK) {Boolean} eid Whether an Emirates ID document has been uploaded.
     * @apiSuccess (200 OK) {Boolean} title_deed Whether a title deed document has been uploaded.
     * @apiSuccess (200 OK) {Integer} [building_id] Included only for users with the `Security` role; indicates assigned building ID.
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *   "success": true,
     *   "error": [],
     *   "data": {
     *     "id": 5,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "john.doe@example.com",
     *     "phone": "+971501234567",
     *     "profile_pic": "https://s3.amazonaws.com/bucket/profile_pics/abc123.jpg",
     *     "email_verified": true,
     *     "phone_verified": true,
     *     "active": true,
     *     "lazim_id": "LZM12345",
     *     "role_id": 3,
     *     "owner_association_id": 2,
     *     "created_at": "2025-10-20T09:35:12.000000Z",
     *     "updated_at": "2025-10-22T14:10:05.000000Z",
     *     "remember_token": null,
     *     "selectType": "globalOa",
     *     "passport": true,
     *     "visa": false,
     *     "eid": true,
     *     "title_deed": false,
     *     "building_id": 1
     *   },
     *   "message": "Profile fetched successfully."
     * }
     *
     * @apiError (401 Unauthorized) Unauthorized Returned when no valid token is provided.
     * @apiErrorExample {json} Unauthorized Response:
     * {
     *   "message": "Unauthenticated."
     * }
     *
     * @apiError (500 Internal Server Error) ServerError Returned when an exception occurs.
     * @apiErrorExample {json} Server Error Response:
     * {
     *   "success": false,
     *   "error": "SQLSTATE[23000]: Integrity constraint violation...",
     *   "data": null,
     *   "message": "Something went wrong."
     * }
     */


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
