<?php

namespace App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;

use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Models\User\User;
use App\Notifications\OverdueInvoiceNotification;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
                            TextInput::make('invoice_number')->disabled(),
                            TextInput::make('date')->disabled(),
                            TextInput::make('description')->disabled(),
                            TextInput::make('rate')->disabled(),
                            Select::make('status')
                                ->required()
                                ->options([
                                    'pending'   => 'Pending',
                                    'paid'      => 'Paid',
                                    'overdue'   => 'Overdue',
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

        Notification::make()
            ->title('Invoice status updated successfully')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return '/app/owner-association-invoices';
    }
}
