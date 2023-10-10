<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\SendOtpMail;
use App\Models\User\User;

class ResetPasswordController extends Controller
{
    /**
     * Generates an OTP for password reset and sends it to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $request->email  The email address of the user requesting a password reset.
     *
     * @return \Illuminate\Http\JsonResponse
     * @return 200  Returns a success message indicating that the OTP has been sent.
     * @return 400  Returns an error message if the email is not registered.
     * @return 500  Returns an error message if there's an issue sending the email.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'The provided email address is not registered in our system.'], 404);
        }

        $passwordReset = DB::table('password_reset_tokens');

        // Generate a 4-digit OTP
        $otp = rand(1000, 9999);

        // Before inserting a new OTP, delete any existing OTP for the given email
        $passwordReset->where('email', $user->email)->delete();

        // Store the OTP in the password_reset_tokens table
        $passwordReset->insert([
            'email' => $user->email,
            'token' => $otp,
            'created_at' => Carbon::now()
        ]);

        // Send the OTP to the user's email
        $when = Carbon::now()->addSeconds(5);
        Mail::to($user->email)->later($when, new SendOtpMail($otp, $user));

        return response()->json(['message' => 'OTP sent to email.']);
    }

    /**
     * Resets the user's password using the provided OTP.
     *
     * @param  \App\Http\Requests\Auth\ResetPasswordRequest  $request
     * @param  string  $request->otp  The OTP sent to the user's email.
     * @param  string  $request->password  The new password.
     * @param  string  $request->password_confirmation  The confirmation of the new password.
     *
     * @return \Illuminate\Http\JsonResponse
     * @return 200  Returns a success message indicating that the password has been reset.
     * @return 400  Returns an error message if the OTP is invalid or expired.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $otpEntry = DB::table('password_reset_tokens')->where('token', $request->otp)->first();

        if (!$otpEntry) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // Check OTP validity (10 minutes)
        // TODO: This is not working need to check
        if (Carbon::parse($otpEntry->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['message' => 'OTP has expired.'], 400);
        }

        $user = User::where('email', $otpEntry->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the OTP entry after use
        DB::table('password_reset_tokens')->where('token', $request->otp)->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
