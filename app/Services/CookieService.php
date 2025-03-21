<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class CookieService
{
    // Static method to set a cookie
    public static function set(string $name, $value, int $minutes = 60)
    {
        // Set the cookie with the specified name, value, and duration in minutes
        Cookie::queue($name, $value, $minutes);
        Log::debug("CookieService - Set cookie: {$name}, Value: {$value}, Expiry: {$minutes} minutes");
    }

    // Static method to get the value of a cookie
    public static function get(string $name)
    {
        $value = Cookie::get($name); // Get cookie value by name
        Log::debug("CookieService - Retrieved cookie: {$name}, Value: " . ($value ?? 'null'));
        return $value;
    }

    // Static method to update a cookie's value
    public static function update(string $name, $value, int $minutes = 60)
    {
        if (Cookie::has($name)) {
            // Update the cookie with the new value and expiry time
            Cookie::queue($name, $value, $minutes);
            Log::debug("CookieService - Updated cookie: {$name}, New Value: {$value}, Expiry: {$minutes} minutes");
        } else {
            throw new \Exception("Cookie '{$name}' does not exist.");
        }
    }

    // Static method to remove a cookie
    public static function remove(string $name)
    {
        if (Cookie::has($name)) {
            // Forget the cookie (remove it)
            Cookie::queue(Cookie::forget($name));
            Log::debug("CookieService - Removed cookie: {$name}");
        } else {
            throw new \Exception("Cookie '{$name}' does not exist.");
        }
    }
}
