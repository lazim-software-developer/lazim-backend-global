<?

namespace App\Helpers;

class UrlHelper
{

    public static function makeUrl($endpoint)
    {
        $key = "SUBDOMAIN_INITIALS";
        $envValue = EnivornmentHelper::getEnvironmentOption($key);

        // Ensure $envValue is not empty and construct the base URL
        $baseUrl = !empty($envValue) ? "{$envValue}{$endpoint}" : $endpoint;
        return $baseUrl;
        // // Ensure $endpoint starts with a slash and concatenate with base URL
        // $endpoint = ltrim($endpoint, '/');
        // return "{$baseUrl}/{$endpoint}";
    }
}
