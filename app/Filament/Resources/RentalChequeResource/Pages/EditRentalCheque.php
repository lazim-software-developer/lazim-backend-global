<?php

namespace App\Filament\Resources\RentalChequeResource\Pages;

use App\Filament\Resources\RentalChequeResource;
use Filament\Resources\Pages\EditRecord;

class EditRentalCheque extends EditRecord
{
    protected static string $resource = RentalChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $comments = is_array($data['comments']) ? $data['comments'] : json_decode($data['comments'], true);
        $numberedComments = array_map(fn($comment, $index) => ($index + 1) . '. ' . $comment, $comments ?? [], array_keys($comments ?? []));
        $data['old_comments'] = implode("\n", $numberedComments);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $oldComments = $record->comments ?? [];
        $oldComments = is_array($oldComments) ? $oldComments : json_decode($oldComments, true);

        if (!empty($data['new_comment'])) {
            $oldComments[] = $data['new_comment'];
        }

        $data['comments'] = json_encode(array_values($oldComments));

        unset($data['old_comments'], $data['new_comment']);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
