<?php

namespace App\Core\Traits;

use Carbon\Carbon;

trait DateTrait
{
    // Method to get the first date of the current month
    public function startOfMonth()
    {
        return Carbon::now()->startOfMonth();
    }

    // Method to get the last date of the current month
    public function endOfMonth()
    {
        return Carbon::now()->endOfMonth();
    }
    // Method to get the current date as a string (YYYY-MM-DD)
    public function toDateString($date)
    {
        return Carbon::parse($date)->toDateString();
    }
}
