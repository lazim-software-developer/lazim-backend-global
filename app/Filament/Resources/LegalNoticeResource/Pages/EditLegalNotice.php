<?php

namespace App\Filament\Resources\LegalNoticeResource\Pages;

use App\Filament\Resources\LegalNoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLegalNotice extends EditRecord
{
    protected static string $resource = LegalNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
