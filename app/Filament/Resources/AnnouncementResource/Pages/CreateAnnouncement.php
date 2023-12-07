<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\Community\Post;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    // public function afterCreate()
    // {
    //     if($this->data['status'] == 'published')
    //     {
    //         Post::where('id', $this->record->id)
    //         ->update(
    //             ['scheduled_at'=>now()]
    //         );;
    //     } 
    // }
}
