<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity as ModelsActivity;

class Activity extends ModelsActivity
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            // Generate a hash of the JSON column
            $jsonHash = md5(json_encode($activity->properties));

            // Check if a record with this hash already exists
            $existingActivity = Activity::where('json_hash', $jsonHash)->first();

            if ($existingActivity) {
                // If a duplicate is found, cancel the creation
                return false;
            }

            // Set the hash value to the activity
            $activity->json_hash = $jsonHash;
        });
    }
}
