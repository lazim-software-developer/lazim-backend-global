<?php

namespace App\Filament\Components;

use App\Helpers\UrlHelper;
use Closure;
use Filament\Navigation\NavigationItem;


class NavigationItemExtended extends NavigationItem
{
    public  function makeCustomUrl(string | Closure | null $url, bool | Closure $shouldOpenInNewTab = false): static
    {
        $this->openUrlInNewTab($shouldOpenInNewTab);
        // $this->url = $url;

        $this->url =  $this->makeUrl($url);
        return $this;
    }

    private function makeUrl($endpoint)
    {
        $key = "SUBDOMAIN_INITIALS";
        $envValue = $this->getEnvironmentOption($key);

        // Ensure $envValue is not empty and construct the base URL
        $baseUrl = !empty($envValue) ? "{$envValue}{$endpoint}" : $endpoint;

        // // Ensure $endpoint starts with a slash and concatenate with base URL
        $baseUrl = ltrim($baseUrl, '/');
        return $baseUrl;
        // return "{$baseUrl}/{$endpoint}";
    }

    private function getEnvironmentOption($key)
    {
        // Example: Fetch the value from environment variables or configuration
        return getenv($key) ?: config("app.{$key}");
    }
}
