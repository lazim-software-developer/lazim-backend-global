<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

trait ThrottlesApiCalls
{
    /**
     * Throttle API call using DB-based locking.
     */
    public function throttleApiCall(string $key, int $intervalInSeconds = 2): bool
    {
        DB::beginTransaction();

        try {
            $record = DB::table('api_rate_limits')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            $now = Carbon::now();

            if ($record && $record->last_called_at) {
                $secondsSinceLastCall = $now->diffInSeconds(Carbon::parse($record->last_called_at));
                if ($secondsSinceLastCall < $intervalInSeconds) {
                    DB::rollBack();
                    return false;
                }
            }

            DB::table('api_rate_limits')
                ->updateOrInsert(
                    ['key' => $key],
                    ['last_called_at' => $now, 'updated_at' => $now]
                );

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Throttling error: ' . $e->getMessage());
            return false;
        }
    }
}
