<?php

namespace App\Filament\Resources\PropertyManagerResource\Pages;

use App\Filament\Resources\PropertyManagerResource;
use App\Jobs\SendInactiveStatusJob;
use App\Models\User\User;
use DB;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPropertyManager extends EditRecord
{
    protected static string $resource = PropertyManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();
        $oldStatus = $record->active;
        $newStatus = $this->data['active'];

        if ($oldStatus !== $newStatus) {
            // Get the user associated with this property manager
            $user = DB::table('users')
                ->where('owner_association_id', $record->id)
                ->first();

            if ($user) {
                // Update the user's active status
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['active' => $newStatus]);

                // If setting to inactive, dispatch notification job
                if (!$newStatus) {
                    SendInactiveStatusJob::dispatch($record);
                }
            }
        }
    }
}
