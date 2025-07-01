<?php

namespace App\Filament\Resources\OAMReceiptsResource\Pages;

use App\Filament\Resources\OAMReceiptsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOAMReceipts extends ListRecords
{
    protected static string $resource = OAMReceiptsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
    // protected function getTableQuery(): Builder
    // {
    //     // dd($this->record);
    //     return parent::getTableQuery()->where('complaint_type', 'suggestions')->where('owner_association_id',auth()->user()?->owner_association_id);
    // }
}
