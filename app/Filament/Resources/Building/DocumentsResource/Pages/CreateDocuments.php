<?php

namespace App\Filament\Resources\Building\DocumentsResource\Pages;

use App\Filament\Resources\Building\DocumentsResource;
use App\Models\Building\Document;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDocuments extends CreateRecord
{
    protected static string $resource = DocumentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
    protected function afterCreate(){
        $jsonValue = json_encode(['comments' => $this->record->remarks,'date'=>now(),

    ]);

        Document::where('id', $this->record->id)
            ->update([
                'comments' => $jsonValue
            ]);

    }
}
