<?php

namespace App\Models\Traits;

use App\Models\ModelHistory;

trait HasHistory
{
    public static function bootHasHistory()
    {
        static::updated(function ($model) {

             // Prevent recursive logging
            if ($model instanceof \App\Models\ModelHistory) {
                return;
            }
            $changes = [];

            foreach ($model->getDirty() as $field => $newValue) {
                $oldValue = $model->getOriginal($field);
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }

            if (!empty($changes)) {
                ModelHistory::create([
                    'historable_type' => $model->getMorphClass(),
                    'historable_id' => $model->id,
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'changes' => $changes,
                ]);
            }
        });
    }
}
