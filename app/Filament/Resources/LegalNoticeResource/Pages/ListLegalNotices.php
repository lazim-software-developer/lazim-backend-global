<?php

namespace App\Filament\Resources\LegalNoticeResource\Pages;

use App\Filament\Resources\LegalNoticeResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLegalNotices extends ListRecords
{
    protected static string $resource = LegalNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
        }
        return parent::getTableQuery();
    }
}
