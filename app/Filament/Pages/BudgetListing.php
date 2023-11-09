<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Master\Service;
use Filament\Pages\Page;

class BudgetListing extends Page
{
    public $building;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'budget-listing/{building}'; // Update the slug to accept a parameter

    protected static string $view = 'filament.pages.budget-listing';

    public function mount(Building $building) // Type-hint the Building model
    {
        $this->building = $building;
    }

    protected function getViewData(): array
    {
        $building = Building::with('services')->find($this->building->id);
        $services = $building->services;

        return [
            'services' => $services,
            'building' => $this->building,
        ];
    }
}
