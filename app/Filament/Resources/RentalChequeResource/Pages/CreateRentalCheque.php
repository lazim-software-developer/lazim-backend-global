<?php

namespace App\Filament\Resources\RentalChequeResource\Pages;

use App\Filament\Resources\RentalChequeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalCheque extends CreateRecord
{
    protected static string $resource = RentalChequeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $oldComments = explode("\n", $data['old_comments'] ?? '');
        $oldComments = array_filter($oldComments, fn($comment) => !empty(trim($comment)));

        if (!empty($data['new_comment'])) {
            $oldComments[] = $data['new_comment'];
        }

        $data['comments'] = json_encode(array_values($oldComments));

        unset($data['old_comments'], $data['new_comment']);
        return $data;
    }
}
