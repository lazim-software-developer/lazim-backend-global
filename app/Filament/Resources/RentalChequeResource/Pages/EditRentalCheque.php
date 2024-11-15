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
        $comments             = is_array($data['comments']) ? $data['comments'] : json_decode($data['comments'], true);
        $data['old_comments'] = implode("\n", $comments ?? []);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $comments = $data['comments'] ?? [];
        $comments = is_array($comments) ? $comments : json_decode($comments, true);

        if (!empty($data['new_comment'])) {
            $comments[] = $data['new_comment'];
        }

        $data['comments'] = json_encode($comments);

        unset($data['old_comments'], $data['new_comment']);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
