<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class CryptoStorageService
{

    private static $DISK_TYPE = "public";

    /**
     * Write data to a JSON file (static method).
     *
     * @param string $filePath
     * @param array $data
     * @return bool
     */
    public static function writeJsonFile(string $filePath, array $data): bool
    {
        // Encode the data to JSON format with pretty print
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        // Encrypt the JSON data
        $encryptedData = Crypt::encrypt($jsonData);

        // Write the encrypted data to the file in the 'public' disk
        return Storage::disk(self::$DISK_TYPE)->put($filePath, $encryptedData);
    }

    /**
     * Read data from a JSON file (static method).
     *
     * @param string $filePath
     * @return array|null
     */
    public static function readJsonFile(string $filePath): ?array
    {
        // Check if the file exists
        if (Storage::disk(self::$DISK_TYPE)->exists($filePath)) {
            // Read the encrypted data from the file
            $encryptedData = Storage::disk(self::$DISK_TYPE)->get($filePath);

            // Decrypt the data
            try {
                $jsonData = Crypt::decrypt($encryptedData);

                // Decode the JSON data into an array
                return json_decode($jsonData, true);
            } catch (\Exception $e) {
                // Handle decryption failure (e.g., invalid encrypted data)
                return null;
            }
        }

        // Return null if the file doesn't exist
        return null;
    }
}
