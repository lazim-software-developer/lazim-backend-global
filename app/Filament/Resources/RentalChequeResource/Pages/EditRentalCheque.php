<?php

namespace App\Filament\Resources\RentalChequeResource\Pages;

use App\Jobs\PaymentLinkResidentEmail;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\RentalChequeResource;

class EditRentalCheque extends EditRecord
{
    protected static string $resource = RentalChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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
        $oldComments = $oldComments ?? [];

        if (!empty($data['new_comment'])) {
            $oldComments[] = $data['new_comment'];
        }

        $data['comments'] = json_encode(array_values($oldComments));

        unset($data['old_comments'], $data['new_comment']);
        return $data;
    }

    protected function beforeSave(): void
    {
        if(in_array($this->record->status,['Paid','Upcoming']) && $this->data['payment_link'] != null && $this->record->payment_link == null){
            $data = $this->data;
            $user = $this->record->rentalDetail->flatTenant->user;

            PaymentLinkResidentEmail::dispatch($user, $data);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
