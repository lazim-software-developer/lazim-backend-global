<?php

namespace App\Filament\Resources\ComplaintsenquiryResource\Pages;

use App\Filament\Resources\ComplaintsenquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintsenquiries extends ListRecords
{
    protected static string $resource = ComplaintsenquiryResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('complaint_type', 'enquiries');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
