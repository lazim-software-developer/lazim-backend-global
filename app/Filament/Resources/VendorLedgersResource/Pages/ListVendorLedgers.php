<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\InvoiceApproval;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VendorLedgersResource;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ListVendorLedgers extends ListRecords
{
    protected static string $resource = VendorLedgersResource::class;
    protected static ?string $title = 'Service provider ledgers';
    protected function getTableQuery(): Builder
    {
        // $vendor_id = Vendor::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id')->toArray();
        // return parent::getTableQuery()->where('status','approved')->whereIn('vendor_id', $vendor_id);
        $buildings = Building::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id');
        $invoiceapprovaloa = InvoiceApproval::whereIn('updated_by',User::where('owner_association_id',auth()->user()?->owner_association_id)->whereIn('role_id',Role::whereIn('name',['MD'])->pluck('id'))->pluck('id'))->where('status','approved')->pluck('invoice_id');
        return parent::getTableQuery()->whereIn('id',$invoiceapprovaloa)->where('status','approved')->whereIn('building_id', $buildings);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            // Action::make('upload')
            //     ->slideOver()
            //     ->color("primary")
            //     ->form([
            //         Select::make('building_id')
            //             ->required()
            //             ->relationship('building', 'name')
            //             ->options(function () {
            //                 if (DB::table('roles')->where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            //                     return Building::all()->pluck('name', 'id');
            //                 } else {
            //                     return Building::where('owner_association_id', auth()->user()?->owner_association_id)
            //                         ->pluck('name', 'id');
            //                 }
            //             })
            //             ->searchable()
            //             ->label('Building Name'),
            //         FileUpload::make('excel_file')
            //             ->label('Service Provider Ledger Data')
            //             ->acceptedFileTypes([
            //                 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
            //                 'application/vnd.ms-excel', // for .xls
            //             ])
            //             ->required(),
            //     ])
            //     ->action(function (array $data) {
            //         $buildingId = $data['building_id'];
            //         $filePath = $data['excel_file']; // This is likely just a file path or name
            //         // Assuming the file is stored in the local disk in a 'budget_imports' directory
            //         $fullPath = storage_path('app/public/' . $filePath);
            //         if (!file_exists($fullPath)) {
            //             Log::error("File not found at path: ", [$fullPath]);
            //             // Handle the error appropriately
            //         }

            //         // Now import using the file path
            //         // Excel::import(new MyClientImport($buildingId), $fullPath);

            //     }),
        ];
    }

    
}
