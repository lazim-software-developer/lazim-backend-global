<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use Exception;

class SessionCryptoService
{
    /**
     * Static method to get decrypted data from the session by key
     */
    public static function get(string $key)
    {
        if (!Session::has($key)) {
            return null; // Return null if the key does not exist
        }

        $encryptedValue = Session::get($key);
        return $encryptedValue ? Crypt::decrypt($encryptedValue) : null;
    }

    /**
     * Static method to add encrypted data to the session
     */
    public static function set(string $key, $value): void
    {
        $encryptedValue = Crypt::encrypt($value); // Encrypt the value
        Session::put($key, $encryptedValue); // Store the encrypted value in the session
        session()->save();
    }

    /**
     * Static method to update encrypted session data
     */
    public static function update(string $key, $value): void
    {
        if (!Session::has($key)) {
            throw new Exception("Session key '{$key}' does not exist.");
        }

        self::set($key, $value); // Reuse the set method to update the value
    }

    /**
     * Static method to remove data from the session
     */
    public static function remove(string $key): void
    {
        if (!Session::has($key)) {
            throw new Exception("Session key '{$key}' does not exist.");
        }

        Session::forget($key); // Remove the data from the session
        session()->save();
    }
}
