<?php

namespace App\Filament\Resources\OAMInvoiceResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OAMInvoiceResource;
use Carbon\Carbon;

class ListOAMInvoices extends ListRecords
{
    protected static string $resource = OAMInvoiceResource::class;
    protected function getTableQuery(): Builder
    {
        $buildingsoflogedin = Building::all()->where('owner_association_id',auth()->user()->owner_association_id)->pluck('id')->toArray();
        return parent::getTableQuery()->whereIn('building_id',$buildingsoflogedin)->where('invoice_status','Defered')->where('invoice_due_date','<',Carbon::now()->toDateString());
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
