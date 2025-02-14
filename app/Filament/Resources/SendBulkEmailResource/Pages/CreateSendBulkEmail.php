<?php

namespace App\Filament\Resources\SendBulkEmailResource\Pages;

use App\Filament\Resources\SendBulkEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSendBulkEmail extends CreateRecord
{
    protected static string $resource = SendBulkEmailResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_association_id'] = auth()->user()->owner_association_id;
        $data['status'] = "pending";

        return $data;
    }
}
