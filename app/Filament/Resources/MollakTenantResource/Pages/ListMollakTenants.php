<?php

namespace App\Filament\Resources\MollakTenantResource\Pages;

use App\Filament\Resources\MollakTenantResource;
use App\Imports\MyClientImport;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMollakTenants extends ListRecords
{
    protected static string $resource = MollakTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            // ExcelImportAction::make()
            //     ->color("primary"),
            ExcelImportAction::make()
            ->slideOver()
            ->color("primary")
            ->use(MyClientImport::class),
        ];
    }
}
