<?php

namespace App\Filament\Resources\TechniciansResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TechniciansResource;

class EditTechnicians extends EditRecord
{
    protected static string $resource = TechniciansResource::class;
    protected function getTableQuery(): Builder
    {
        if(auth()->user()->id != 1) 
        {
            return parent::getTableQuery()->where('is_announcement',0)->where('owner_association_id',auth()->user()->owner_association_id);
        }
        return parent::getTableQuery()->where('is_announcement',0);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
