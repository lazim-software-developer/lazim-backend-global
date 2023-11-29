<?php

namespace App\Filament\Pages;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMInvoice;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class DelinquentOwners extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.delinquent-owners';

    protected static ?string $slug = 'delinquent-owners';

    protected function getViewData(): array
    {
        // Fetch all buildigns for logged in users
        $buildings = Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id');

        $currentYear = Carbon::now()->year;

        $unpaidInvoices = OAMInvoice::query()
            ->whereYear('invoice_due_date', $currentYear)
            ->where('invoice_due_date', '<', Carbon::now())
            ->where('invoice_status', '!=', 'paid') // adjust based on your actual 'paid' status
            ->pluck('flat_id');

        $flatsWithUnpaidInvoices = Flat::whereIn('id', $unpaidInvoices)->get();

        $delinquentOwners = [];

        foreach ($flatsWithUnpaidInvoices as $flat) {
            $ownerData = FlatOwners::where(['flat_id' => 45])->first();
            $owner = ApartmentOwner::where(['id' => $ownerData->id])->first();

            $delinquentOwners[] = [
                'owner_id' => $owner?->id,
                'owner_name' => $owner?->name,
                'flat_property_number' => $flat?->property_number,
            ];
        }

        return [
            'buildingIds' => $buildings,
            'delinquentOwners' => $delinquentOwners
        ];
    }
}
