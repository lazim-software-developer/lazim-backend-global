<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GenericHttpService
{
    //protected static $baseUrl;
    public static $TOKEN_KEY = "API_TOKEN_KEY";
    public static $TOKEN_FILE_PATH = "security/authentication.json";

    public static function initialize()
    {
        // Set your base URL (e.g., the API server's URL)
        // self::$baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000/api');
    }

    // GET request
    public static function get(string $url, array $headers = [])
    {
        return self::makeRequest('GET', $url, $headers);
    }

    // POST request
    public static function post(string $url, array $data = [], array $headers = [])
    {
        return self::makeRequest('POST', $url, $headers, $data);
    }

    // DELETE request
    public static function delete(string $url, array $headers = [])
    {
        return self::makeRequest('DELETE', $url, $headers);
    }

    // Making a generic HTTP request with retries and custom timeout
    private static function makeRequest(string $method, string $url, array $headers = [], array $data = [])
    {
        $headers = self::mergeHeaders($headers);

        $attempts = 3;  // Retry 3 times before failing

        try {
            // Attempt to make the request with retries
            $response = self::makeHttpRequest($method, $url, $headers, $data, $attempts);
            // return [
            //     'success' => true,
            //     'data' => $response->json(),
            //     'message' => 'Request successful.'
            // ];
            return $response->json();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                //'message' => 'Something went wrong. Please try again later.',
                'message' => $e->getMessage(),
                'errors' => [],
            ];
        }
    }

    // Making HTTP request with retry mechanism
    private static function makeHttpRequest(string $method, string $url, array $headers, array $data, int $attempts)
    {
        $baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8001/api');
        $response = null;
        while ($attempts-- > 0) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(120)  // Set the timeout to 60 seconds
                    // ->$method(self::$baseUrl + "{$url}", $data);
                    ->$method("{$baseUrl}{$url}", $data);

                if ($response->successful()) {
                    return $response;  // Return the response if successful
                }
            } catch (\Exception $e) {
                if ($attempts <= 0) {
                    throw $e;  // Re-throw the exception if no more attempts left
                }
            }
            sleep(1); // Optional delay between retries
        }

        throw new \Exception('Request failed after multiple attempts.');
    }

    // Merge headers (add Authorization token if available)
    private static function mergeHeaders(array $headers)
    {
        // $fileContent = CryptoStorageService::readJsonFile(self::$TOKEN_FILE_PATH);
        // $token = $fileContent ? $fileContent["token"] : null;

        $token = SessionCryptoService::get(self::$TOKEN_KEY);

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        // Additional default headers
        $headers['Accept'] = 'application/json';

        return $headers;
    }
}
