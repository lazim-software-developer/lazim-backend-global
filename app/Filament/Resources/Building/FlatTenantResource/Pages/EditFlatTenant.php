<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use DB;
use Filament\Resources\Pages\EditRecord;

class EditFlatTenant extends EditRecord
{
    protected static string $resource = FlatTenantResource::class;
    protected static ?string $title   = 'Resident';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $makaniNumber = DB::table('documents')
            ->where('name', 'Makani number')
            ->where('flat_id', $data['flat_id'])
            ->value('url');
        $data['makani_number_url'] = $makaniNumber;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        DB::table('documents')
            ->where('name', 'Makani number')
            ->where('flat_id', $data['flat_id'])
            ->update(['url' => $data['makani_number_url']]);
        return $data;
    }
}
