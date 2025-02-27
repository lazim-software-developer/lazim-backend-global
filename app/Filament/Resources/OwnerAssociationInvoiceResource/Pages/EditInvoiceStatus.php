<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Models\Building\Building;
use App\Models\User\User;
use App\Notifications\OverdueInvoiceNotification;
use DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceStatus extends EditRecord
{
    protected static string $resource = OwnerAssociationInvoiceResource::class;

    protected static string $view = 'filament.pages.edit-invoice-status';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Grid::make(['default' => 2])
                        ->schema([
                            TextInput::make('date')->disabled()->label('Invoice Date'),
                            TextInput::make('due_date')->disabled()->label('Invoice Due Date'),
                            TextInput::make('invoice_number')->disabled(),
                            TextInput::make('type')->disabled(),
                            Select::make('building_id')
                                ->options(function ($state) {

                                    $buildingIds = DB::table('building_owner_association')
                                        ->where('owner_association_id', auth()->user()->owner_association_id)
                                        ->where('active', true)
                                        ->pluck('building_id');

                                    return Building::whereIn('id', $buildingIds)
                                        ->pluck('name', 'id');

                                })
                                ->disabled()
                                ->visible(function (callable $get) {
                                    if ($get('type') == 'building') {
                                        return true;
                                    }
                                    return false;
                                })
                                ->preload()
                                ->live()
                                ->label('Building Name'),

                            TextInput::make('mode_of_payment')->disabled(),
                            TextInput::make('job')
                                ->disabled()
                                ->label(function () {
                                    if (auth()->user()->role->name == 'Property Manager') {
                                        return 'Service/Job ';
                                    }
                                }),
                            Select::make('month')->required()
                                ->disabled()
                                ->options([
                                    'january'   => 'January',
                                    'february'  => 'February',
                                    'march'     => 'March',
                                    'april'     => 'April',
                                    'may'       => 'May',
                                    'june'      => 'June',
                                    'july'      => 'July',
                                    'august'    => 'August',
                                    'september' => 'September',
                                    'october'   => 'October',
                                    'november'  => 'November',
                                    'december'  => 'December',
                                ]),
                            TextInput::make('rate')->disabled(),
                            TextInput::make('trn')->disabled()->label('TRN'),
                            Textarea::make('description')->disabled(),
                            Select::make('status')
                                ->required()
                                ->options([
                                    'pending' => 'Pending',
                                    'paid'    => 'Paid',
                                    'overdue' => 'Overdue',
                                ]),

                        ]),
                ]),
        ])->statePath('data');
    }

    protected function afterSave(): void
    {
        if ($this->data['status'] === 'overdue') {
            $propertyManager = User::whereHas('role', function ($query) {
                $query->where('name', 'Property Manager');
            })
                ->where('owner_association_id', $this->record->owner_association_id)
                ->first();

            if ($propertyManager) {
                $propertyManager->notify(new OverdueInvoiceNotification($this->record));
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return '/app/owner-association-invoices';
    }
}
