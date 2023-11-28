<?php

namespace App\Filament\Resources\LedgersResource\Pages;

use App\Filament\Resources\LedgersResource;
use App\Filament\Resources\VendorLedgersResource;
use App\Models\Accounting\OAMReceipts;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListReceipts extends Page 
{
    protected static string $resource = LedgersResource::class;

    protected static string $view = 'filament.resources.ledgers-resource.pages.list-receipts';

    protected static ?string $modelLabel = 'Receipts';
    
    // public static function table(Table $table):Table {
    //     return $table
    //         ->columns([
    //             TextColumn::make('receipt_date')
    //                 ->label('Date')
    //                 ->date(),
    //             ]);
    // }
}
