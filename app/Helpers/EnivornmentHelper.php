<?

namespace App\Helpers;

class EnivornmentHelper
{

    // Add the helper method before this provider logic
    public static function getEnvironmentOption($key)
    {
        // Example: Fetch the value from environment variables or configuration
        return getenv($key) ?: config("app.{$key}");
    }
}
