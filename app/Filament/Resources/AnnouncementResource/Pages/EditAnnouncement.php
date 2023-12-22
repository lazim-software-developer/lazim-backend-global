<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Community\Post;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    // public function beforeSave()
    // {
    //     if($this->record->status == 'published')
    //     {
    //         Post::where('id', $this->data['id'])
    //             ->update([
    //                 'scheduled_at' => now(),
    //             ]);
    //     }
    // }

}
